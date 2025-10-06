<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'payment_id',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'billing_name',
        'billing_email',
        'billing_country',
        'billing_city',
        'billing_line1',
        'billing_postal_code',
        'status',
        'error_message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

}
