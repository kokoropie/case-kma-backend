<?php 
namespace App\Enum;

enum OrderStatus: string {
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case COMPLETED = 'delivered';
    case CANCELLED = 'cancelled';
}