<?php

namespace App\Services\Shipping\Exceptions;

/**
 * NimbusPost answered and refused the request (documented as {"status": false, "message": "..."}), e.g. invalid
 * consignee data or "Unable to cancel". The message is NimbusPost's own text, shown to admins only.
 */
class NimbusPostRejected extends NimbusPostException {}
