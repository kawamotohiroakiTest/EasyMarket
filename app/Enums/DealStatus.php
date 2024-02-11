<?php

namespace App\Enums;

enum DealStatus: string
{
    case Listing = 'listing';
    case Purchased = 'purchased';
    case Shipping = 'shipping';
    case Completed = 'completed';
    case Canceled = 'canceled';
}