<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'vehicle_id','inspection_date','odometer_reading','status','job_code','images','remarks','insurance_claim','repair_type','created_by','vehicle_status','vehicle_condition','rental_id','service_type',
    ];

    protected $casts = [
        'images' => 'array',
        'created_at' => 'datetime',

    ];

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }

    public function faults()
    {
        return $this->belongsToMany(Fault::class, 'inspection_faults')
            ->withPivot('status','percentage')
            ->withTimestamps();
    }

    public function garageReports() {
        return $this->hasMany(GarageReport::class);
    }

    public function fleetDecisions()
    {
        return $this->hasMany(FleetDecision::class, 'inspection_id');
    }

    public function accountantReviews()
    {
        return $this->hasMany(AccountantReview::class, 'inspection_id', 'id');
    }

    public function gm_reviews()
    {
        return $this->hasMany(GMReview::class, 'inspection_id');
    }

    public function latest_gm_review()
    {
        return $this->hasOne(GMReview::class)->latestOfMany();
    }

    public function latest_gm_dispatch()
    {
        return $this->hasOne(GmDispatch::class)->latestOfMany();
    }

        public function gmWorkStatuses()
    {
        return $this->hasMany(GMWorkStatus::class, 'inspection_id');
    }

    public function fleetPostCheck()
    {
        return $this->hasOne(FleetPostCheck::class);
    }

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }

    public function gmReviews()
    {
        return $this->hasMany(GMReview::class);
    }

    public function issues()
    {
        return $this->hasMany(IssueInventory::class, 'inspection_id'); 
    }

    public function vehicleFaults()
    {
        return $this->hasMany(Fault::class, 'inspection_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tempInspections()
    {
        return $this->hasMany(TempInspection::class);
    }

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
