<?php

namespace CMSCore\Enums;

enum CategoryType: string
{
    case BLOG_CATEGORY  = 'blog_cat';
    case BRAND_CATEGORY = 'brand_cat';
    case BLOG_TAG       = 'tag_cat';
    case EVENT_CATEGORY = 'event_cat';
    case EVENT_TAG      = 'event_tag';
}
