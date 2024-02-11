<?php

namespace App\Enums;

enum DealEventEventType: string
{
    case Listing = 'listing';
    case Purchase = 'purchase';
    case ReportDelivery = 'report_delivery';
    case ReportReceipt = 'report_receipt';
    case Cancel = 'cancel';
}
