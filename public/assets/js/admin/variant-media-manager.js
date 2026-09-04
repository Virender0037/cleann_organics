/**
 * Variant Media Manager — unified drag/drop image+video gallery for the
 * Product Variant create/edit admin screens.
 *
 * No server request happens here except deleting an already-saved item;
 * everything else (new uploads, reordering, primary selection) rides on
 * the existing variant create/update form submit, via three hidden
 * inputs this script keeps in sync: new_media[] (the actual files),
 * media_order (the unified sequence, "e12,n0,e15,n1,..."), and
 * primary_selector ("existing:12" or "new:0").
 */
(function () {
  'use strict';

  function initVariantMediaManager(root) {
    var config = JSON.parse(root.dataset.config || '{}');
    var deleteUrlTemplate = root.dataset.deleteUrlTemplate || null;

    var dropzone = root.querySelector('#vmmDropzone');
    var fileInput = root.querySelector('#vmmFileInput');
    var gallery = root.querySelector('#vmmGallery');
    var emptyState = root.querySelector('#vmmEmptyState');
    var errorBox = root.querySelector('#vmmError');
    var imageCountEl = root.querySelector('#vmmImageCount');
    var videoCountEl = root.querySelector('#vmmVideoCount');
    var mediaOrderInput = root.querySelector('#vmmMediaOrder');
    var primarySelectorInput = root.querySelector('#vmmPrimarySelector');
    var form = root.closest('form');
    var csrfInput = form ? form.querySelector('input[name="_token"]') : null;
    var csrfToken = csrfInput ? csrfInput.value : '';

    var items = [];
    var newItemCounter = 0;
    var dragSourceLocalId = null;

    try {
      var existing = JSON.parse(root.dataset.existingMedia || '[]');
      items = existing.map(function (row) {
        return {
          localId: 'e' + row.id,
          kind: 'existing',
          id: row.id,
          type: row.type,
          url: row.url,
          isPrimary: !!row.isPrimary,
        };
      });
    } catch (e) {
      items = [];
    }

    function extensionOf(filename) {
      var parts = filename.split('.');
      return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }

    function typeForFile(file) {
      var ext = extensionOf(file.name);
      if (config.imageExtensions.indexOf(ext) !== -1) return 'image';
      if (config.videoExtensions.indexOf(ext) !== -1) return 'video';
      return null;
    }

    function showError(message) {
      errorBox.textContent = message;
      errorBox.style.display = message ? 'block' : 'none';
    }

    function counts() {
      var images = 0;
      var videos = 0;
      items.forEach(function (item) {
        if (item.type === 'image') images++;
        else if (item.type === 'video') videos++;
      });
      return { images: images, videos: videos };
    }

    function addFiles(fileList) {
      showError('');
      var c = counts();
      var rejected = [];

      Array.prototype.forEach.call(fileList, function (file) {
        var type = typeForFile(file);

        if (!type) {
          rejected.push(file.name + ' (unsupported file type)');
          return;
        }

        var maxKb = type === 'image' ? config.maxImageKb : config.maxVideoKb;
        if (file.size > maxKb * 1024) {
          rejected.push(file.name + ' (larger than ' + Math.round(maxKb / 1024) + 'MB)');
          return;
        }

        if (type === 'image' && c.images >= config.maxImages) {
          rejected.push(file.name + ' (image limit of ' + config.maxImages + ' reached)');
          return;
        }

        if (type === 'video' && c.videos >= config.maxVideos) {
          rejected.push(file.name + ' (video limit of ' + config.maxVideos + ' reached)');
          return;
        }

        items.push({
          localId: 'new-' + (newItemCounter++),
          kind: 'new',
          type: type,
          file: file,
          url: URL.createObjectURL(file),
          isPrimary: false,
        });

        if (type === 'image') c.images++;
        else c.videos++;
      });

      if (rejected.length) {
        showError('Not added: ' + rejected.join(', '));
      }

      render();
    }

    function removeExisting(item) {
      if (!deleteUrlTemplate) return;

      if (!window.confirm('Delete this ' + item.type + '? This cannot be undone.')) {
        return;
      }

      var url = deleteUrlTemplate.replace('__IMAGE_ID__', item.id);

      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
        .then(function (response) {
          if (!response.ok) throw new Error('Delete failed');
          return response.json();
        })
        .then(function (data) {
          items = items.filter(function (i) {
            return i.localId !== item.localId;
          });

          if (data.promoted_primary_id) {
            items.forEach(function (i) {
              i.isPrimary = i.kind === 'existing' && i.id === data.promoted_primary_id;
            });
          }

          render();
        })
        .catch(function () {
          showError('Could not delete this item. Please try again.');
        });
    }

    function removeItem(localId) {
      var item = items.find(function (i) {
        return i.localId === localId;
      });

      if (!item) return;

      if (item.kind === 'existing') {
        removeExisting(item);
        return;
      }

      URL.revokeObjectURL(item.url);
      items = items.filter(function (i) {
        return i.localId !== localId;
      });
      render();
    }

    function setPrimary(localId) {
      var target = items.find(function (i) {
        return i.localId === localId;
      });

      if (!target || target.type !== 'image') return;

      items.forEach(function (i) {
        i.isPrimary = i.localId === localId;
      });

      render();
    }

    function ensureFallbackPrimary() {
      if (items.some(function (i) { return i.isPrimary; })) return;

      var firstImage = items.find(function (i) { return i.type === 'image'; });
      if (firstImage) firstImage.isPrimary = true;
    }

    function buildCard(item) {
      var card = document.createElement('div');
      card.className = 'vmm-card' + (item.isPrimary ? ' vmm-card--primary' : '');
      card.draggable = true;
      card.dataset.localId = item.localId;

      var media;
      if (item.type === 'video') {
        media = document.createElement('video');
        media.src = item.url;
        media.muted = true;
        media.preload = 'metadata';
        media.className = 'vmm-card-media';

        var badge = document.createElement('span');
        badge.className = 'vmm-card-video-badge';
        badge.innerHTML = '<i class="ph ph-play"></i>';
        card.appendChild(badge);
      } else {
        media = document.createElement('img');
        media.src = item.url;
        media.alt = 'Variant media';
        media.className = 'vmm-card-media';
      }
      card.appendChild(media);

      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'vmm-card-remove';
      removeBtn.setAttribute('aria-label', 'Remove');
      removeBtn.innerHTML = '<i class="ph ph-x"></i>';
      removeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        removeItem(item.localId);
      });
      card.appendChild(removeBtn);

      var footer = document.createElement('div');
      footer.className = 'vmm-card-footer';

      if (item.kind === 'new') {
        var pendingTag = document.createElement('span');
        pendingTag.className = 'vmm-card-pending-tag';
        pendingTag.textContent = 'New';
        footer.appendChild(pendingTag);
      } else {
        footer.appendChild(document.createElement('span'));
      }

      if (item.type === 'image') {
        if (item.isPrimary) {
          var primaryBadge = document.createElement('span');
          primaryBadge.className = 'vmm-primary-badge';
          primaryBadge.textContent = 'Primary';
          footer.appendChild(primaryBadge);
        } else {
          var primaryBtn = document.createElement('button');
          primaryBtn.type = 'button';
          primaryBtn.className = 'vmm-primary-toggle';
          primaryBtn.textContent = 'Set primary';
          primaryBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            setPrimary(item.localId);
          });
          footer.appendChild(primaryBtn);
        }
      }

      card.appendChild(footer);

      card.addEventListener('dragstart', function (e) {
        dragSourceLocalId = item.localId;
        card.classList.add('vmm-card--dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('application/x-vmm-reorder', item.localId);
      });

      card.addEventListener('dragend', function () {
        card.classList.remove('vmm-card--dragging');
        dragSourceLocalId = null;
      });

      card.addEventListener('dragover', function (e) {
        if (!dragSourceLocalId || dragSourceLocalId === item.localId) return;
        e.preventDefault();
        card.classList.add('vmm-card--dragover');
      });

      card.addEventListener('dragleave', function () {
        card.classList.remove('vmm-card--dragover');
      });

      card.addEventListener('drop', function (e) {
        e.preventDefault();
        card.classList.remove('vmm-card--dragover');

        var sourceId = e.dataTransfer.getData('application/x-vmm-reorder');
        if (!sourceId || sourceId === item.localId) return;

        var fromIndex = items.findIndex(function (i) { return i.localId === sourceId; });
        var toIndex = items.findIndex(function (i) { return i.localId === item.localId; });
        if (fromIndex === -1 || toIndex === -1) return;

        var moved = items.splice(fromIndex, 1)[0];
        items.splice(toIndex, 0, moved);
        render();
      });

      return card;
    }

    function syncFileInput() {
      var dt = new DataTransfer();
      items.forEach(function (item) {
        if (item.kind === 'new') dt.items.add(item.file);
      });
      fileInput.files = dt.files;
    }

    function syncHiddenFields() {
      var newIndex = 0;
      var tokens = [];
      var primaryToken = '';

      items.forEach(function (item) {
        if (item.kind === 'existing') {
          tokens.push('e' + item.id);
          if (item.isPrimary) primaryToken = 'existing:' + item.id;
        } else {
          tokens.push('n' + newIndex);
          if (item.isPrimary) primaryToken = 'new:' + newIndex;
          newIndex++;
        }
      });

      mediaOrderInput.value = tokens.join(',');
      primarySelectorInput.value = primaryToken;
    }

    function render() {
      ensureFallbackPrimary();

      gallery.innerHTML = '';
      items.forEach(function (item) {
        gallery.appendChild(buildCard(item));
      });

      emptyState.style.display = items.length ? 'none' : 'block';

      var c = counts();
      imageCountEl.textContent = c.images;
      videoCountEl.textContent = c.videos;
      imageCountEl.classList.toggle('vmm-limit-reached', c.images >= config.maxImages);
      videoCountEl.classList.toggle('vmm-limit-reached', c.videos >= config.maxVideos);

      syncFileInput();
      syncHiddenFields();
    }

    dropzone.addEventListener('click', function () {
      fileInput.click();
    });

    dropzone.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        fileInput.click();
      }
    });

    fileInput.addEventListener('change', function () {
      // Use a throwaway list: fileInput itself is rebuilt by syncFileInput()
      // on every render, so its own change event must not feed back in.
      var picked = Array.prototype.slice.call(fileInput.files);
      addFiles(picked);
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
      dropzone.addEventListener(evt, function (e) {
        if (!e.dataTransfer || !e.dataTransfer.types || e.dataTransfer.types.indexOf('Files') === -1) return;
        e.preventDefault();
        dropzone.classList.add('vmm-dropzone--active');
      });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
      dropzone.addEventListener(evt, function () {
        dropzone.classList.remove('vmm-dropzone--active');
      });
    });

    dropzone.addEventListener('drop', function (e) {
      if (!e.dataTransfer || !e.dataTransfer.files || !e.dataTransfer.files.length) return;
      e.preventDefault();
      addFiles(e.dataTransfer.files);
    });

    if (form) {
      form.addEventListener('submit', function () {
        syncFileInput();
        syncHiddenFields();
      });
    }

    render();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('variantMediaManager');
    if (root) initVariantMediaManager(root);
  });
})();
