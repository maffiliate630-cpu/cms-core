<?php

namespace CMSCore\Enums;

enum HttpRedirectionStatus: int
{
    case TEMPORARY = 307;
    case PERMANENT = 301;
}
