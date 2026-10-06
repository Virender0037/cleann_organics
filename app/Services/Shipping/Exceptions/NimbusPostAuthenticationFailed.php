<?php

namespace App\Services\Shipping\Exceptions;

/** users/login refused the credentials (documented 401 "Invalid email or password"). */
class NimbusPostAuthenticationFailed extends NimbusPostException {}
