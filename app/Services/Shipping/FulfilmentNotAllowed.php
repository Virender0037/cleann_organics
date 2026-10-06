<?php

namespace App\Services\Shipping;

use RuntimeException;

/** A fulfilment action refused by our own rules (payment gate, duplicate, wrong state). Message is admin-safe. */
class FulfilmentNotAllowed extends RuntimeException {}
