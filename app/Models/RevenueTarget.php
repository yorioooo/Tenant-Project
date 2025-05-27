<?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Model;

   class RevenueTarget extends Model
   {
       protected $table = 'revenue_targets';

       protected $fillable = [
           'target_amount',
           'year',
           // Add other fields as needed
       ];

       protected $casts = [
           'target_amount' => 'integer',
           // Add other casts if necessary
       ];
   }