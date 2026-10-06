<?php

namespace App\Services\Shipping\Exceptions;

/** A 2xx response that is not the documented JSON shape (missing `status`, missing AWB, non-JSON…). */
class NimbusPostMalformedResponse extends NimbusPostException {}
