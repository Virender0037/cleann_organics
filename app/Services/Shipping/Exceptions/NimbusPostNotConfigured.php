<?php

namespace App\Services\Shipping\Exceptions;

/** NIMBUSPOST_ENABLED is false, or credentials / pickup address are blank. Nothing was sent. */
class NimbusPostNotConfigured extends NimbusPostException {}
