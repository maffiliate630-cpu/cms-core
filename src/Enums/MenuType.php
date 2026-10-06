<?php

namespace CMSCore\Enums;

enum MenuType: string
{
    case INTERNAL_LINK = 'internal_link';
    case EXTERNAL_LINK = 'external_link';
}
