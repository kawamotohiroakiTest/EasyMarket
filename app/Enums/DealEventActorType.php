<?php

namespace App\Enums;

enum DealEventActorType: string
{
    case Seller = 'seller';
    case Buyer = 'buyer';
    case Admin = 'admin';
}