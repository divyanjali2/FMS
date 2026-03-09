<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';
    protected $guarded = [];
    
    protected $fillable = [
        'reg_no', 'vehicle_type_id', 'make', 'model', 'year_of_manufacture',
        'color', 'vin', 'engine_no', 'fuel_type_id', 'transmission_id',
        'seating_capacity', 'odometer_at_registration', 'ownership_type_id',
        'owner_name', 'lease_start', 'lease_end', 'insurance_provider',
        'insurance_policy_no', 'insurance_expiry', 'emission_test_expiry',
        'revenue_license_expiry', 'purchase_price', 'purchase_date',
        'depreciation_rate', 'current_value','loan_emi_details',
        'revenue_license_file', 'insurance_file','emi_date','emi_number',
        'emission_test_file', 'other_doc_file','vehicle_front', 'vehicle_back', 'vehicle_left', 'vehicle_right','company_id','owner_phone','status','remarks','vehicle_category_id'
    ];

    protected $casts = [
        'emi_date' => 'date',
    ];

    /**
     * Get the vehicle type associated with the vehicle.
     */
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function vehicleCategory()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    /**
     * Get the fuel type associated with the vehicle.
     */
    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id');
    }

    /**
     * Get the transmission associated with the vehicle.
     */
    public function transmission()
    {
        return $this->belongsTo(Transmission::class, 'transmission_id');
    }

    /**
     * Get the ownership type associated with the vehicle.
     */
    public function ownershipType()
    {
        return $this->belongsTo(OwnershipType::class, 'ownership_type_id');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cashier()
    {
        return $this->hasOne(Cashier::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function latestInspection()
    {
        return $this->hasOne(Inspection::class)->latestOfMany();
    }

    public function freezes()
    {
        return $this->hasMany(VehicleFreeze::class);
    }
}
