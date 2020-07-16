<?php

namespace Sunnysideup\HealthCheckProvider\Checks\Response;

class IsThereServerErrorPage extends IsTherePageNotFoundPage
{
    private static $error_code = 500;
}
