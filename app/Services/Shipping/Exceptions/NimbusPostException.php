<?php

namespace App\Services\Shipping\Exceptions;

use RuntimeException;

/**
 * Base class for every NimbusPost failure. getMessage() is safe to show an ADMIN (never contains credentials or the
 * raw response); customers only ever see generic wording chosen by the caller.
 */
class NimbusPostException extends RuntimeException {}
