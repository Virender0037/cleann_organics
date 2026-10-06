<?php

namespace App\Services\Shipping\Exceptions;

/**
 * Timeout, connection failure or 5xx: the outcome is UNKNOWN. Callers must never read this as "not serviceable" or
 * "not booked" — e.g. a create-shipment timeout may still have booked an AWB on NimbusPost's side.
 */
class NimbusPostUnavailable extends NimbusPostException {}
