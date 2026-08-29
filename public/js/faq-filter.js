(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var filterButtons = document.querySelectorAll('.faq__filter-btn');
    var searchInput = document.getElementById('faqSearchInput');
    var items = document.querySelectorAll('.faq__item');
    var countEl = document.getElementById('faqCount');
    var noTopicResults = document.getElementById('faqNoTopicResults');
    var noSearchResults = document.getElementById('faqNoSearchResults');

    if (!filterButtons.length || !items.length) {
      return;
    }

    var activeTopic = 'all';

    function render() {
      var searchTerm = (searchInput ? searchInput.value : '').trim().toLowerCase();
      var visibleCount = 0;

      items.forEach(function (item) {
        var matchesTopic = activeTopic === 'all' || item.dataset.topic === activeTopic;
        var matchesSearch = searchTerm === '' || item.dataset.search.indexOf(searchTerm) !== -1;
        var visible = matchesTopic && matchesSearch;

        item.style.display = visible ? '' : 'none';

        if (visible) {
          visibleCount++;
        }
      });

      if (countEl) {
        countEl.textContent = visibleCount === 1 ? '1 question found' : visibleCount + ' questions found';
      }

      if (noSearchResults) {
        noSearchResults.hidden = !(visibleCount === 0 && searchTerm !== '');
      }

      if (noTopicResults) {
        noTopicResults.hidden = !(visibleCount === 0 && searchTerm === '' && activeTopic !== 'all');
      }
    }

    filterButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        filterButtons.forEach(function (btn) {
          btn.classList.remove('active');
          btn.classList.add('button--outline');
        });

        button.classList.add('active');
        button.classList.remove('button--outline');

        activeTopic = button.dataset.topic;
        render();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', render);
    }

    render();
  });
})();
