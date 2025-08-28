
<?php

class ExpenseCategories {
    
    public static function getAllCategories() {
        return [
            'normal' => [
                'transportation' => [
                    'name' => 'Transportation',
                    'categories' => [
                        'car_purchase_loan', 'car_insurance', 'fuel', 'maintenance', 'repairs',
                        'parts_accessories', 'registration_licensing', 'roadside_assistance',
                        'tolls', 'parking', 'public_transit', 'rideshare_taxi', 'bike_scooter',
                        'car_wash_detailing', 'car_membership', 'driver_license_renewals', 'fines', 'other'
                    ]
                ],
                'accommodation_housing' => [
                    'name' => 'Accommodation / Housing',
                    'categories' => [
                        'rent', 'mortgage', 'property_taxes', 'home_insurance', 'electricity',
                        'heating_hydro', 'water_sewer', 'garbage_recycling', 'internet',
                        'cable_streaming_home', 'mobile_landline', 'hoa_condo_fees', 'security_system',
                        'pest_control', 'cleaning_services', 'repairs_maintenance', 'renovations',
                        'furniture', 'appliances', 'tools', 'storage_unit', 'moving_costs',
                        'landscaping_snow_removal', 'other'
                    ]
                ],
                'entertainment_leisure' => [
                    'name' => 'Entertainment & Leisure',
                    'categories' => [
                        'dining_out', 'cafes', 'snacks_treats', 'movies', 'concerts', 'theater',
                        'museums', 'sports_events', 'activity_fees', 'subscriptions', 'gaming',
                        'hobbies_crafts', 'books', 'streaming_rentals', 'nightlife', 'events_festivals',
                        'photography_gear', 'courses_workshops', 'gifts_non_essential', 'other'
                    ]
                ],
                'health_wellness' => [
                    'name' => 'Health & Wellness',
                    'categories' => [
                        'gym_membership', 'fitness_classes', 'personal_training', 'home_fitness_equipment',
                        'medicines', 'vitamins_supplements', 'prescriptions', 'pharmacy_fees',
                        'gp_family_doctor', 'specialists', 'hospital_er', 'urgent_care', 'dental',
                        'vision', 'hearing', 'mental_health', 'physiotherapy', 'chiropractor',
                        'massage_therapy', 'acupuncture', 'alternative_naturopathy', 'lab_tests',
                        'medical_devices', 'health_insurance_premiums', 'travel_vaccines', 'other'
                    ]
                ],
                'essentials' => [
                    'name' => 'Essentials',
                    'categories' => [
                        'groceries', 'household_supplies', 'toiletries_personal_care', 'laundry_dry_cleaning',
                        'baby_supplies', 'school_supplies', 'tuition_fees', 'childcare_babysitting',
                        'transportation_passes_work', 'pet_food_basic_care', 'basic_clothing_shoes',
                        'work_uniforms_tools', 'cloud_storage_phone_essential', 'banking_fees',
                        'taxes_filing_fees', 'postage_shipping_essentials', 'community_dues', 'other'
                    ]
                ],
                'non_essentials' => [
                    'name' => 'Non-Essentials',
                    'categories' => [
                        // Memberships
                        'clubs_membership', 'premium_streaming', 'subscription_boxes', 'premium_apps',
                        'creator_memberships', 'magazines_newspapers', 'specialty_gyms', 'vip_programs',
                        'game_passes', 'membership_other',
                        // Non-Memberships
                        'fashion_accessories', 'designer_items', 'luxury_electronics', 'collectibles',
                        'hobbies_special_gear', 'decor', 'non_essential_gifts', 'travel_splurges',
                        'event_splurges', 'cosmetics_luxury_care', 'impulse_buys', 'non_membership_other'
                    ]
                ]
            ],
            'travel' => [
                'transportation_travel' => [
                    'name' => 'Transportation (Travel)',
                    'categories' => [
                        'flights', 'trains', 'buses_coaches', 'shuttles', 'car_rental', 'fuel',
                        'taxis_rideshare', 'ferries_boats', 'cruises', 'city_transport_passes',
                        'baggage_fees', 'seat_upgrade_fees', 'airport_parking', 'tolls',
                        'travel_insurance_transport', 'other'
                    ]
                ],
                'accommodation_travel' => [
                    'name' => 'Accommodation (Travel)',
                    'categories' => [
                        'hotels', 'hostels', 'guesthouses', 'vacation_rentals', 'resorts', 'motels',
                        'campsites', 'overnight_trains_boats', 'day_rooms', 'resort_fees',
                        'city_lodging_taxes', 'other'
                    ]
                ],
                'food_dining_travel' => [
                    'name' => 'Food & Dining (Travel)',
                    'categories' => [
                        'restaurants', 'cafes', 'street_food', 'groceries', 'delivery_apps',
                        'room_service', 'snacks', 'water_beverages', 'specialty_dining_experiences', 'other'
                    ]
                ],
                'entertainment_activities_travel' => [
                    'name' => 'Entertainment & Activities (Travel)',
                    'categories' => [
                        'tours', 'landmarks_museums', 'theme_amusement_parks', 'beaches_pool_passes',
                        'outdoor_activities', 'gear_rental', 'shows_concerts_nightlife', 'classes_workshops',
                        'souvenirs_shopping', 'photography_permits', 'local_sim_roaming_apps', 'other'
                    ]
                ],
                'essentials_travel' => [
                    'name' => 'Essentials (Travel)',
                    'categories' => [
                        'visas_passport_fees', 'currency_exchange_atm_fees', 'sim_esim_roaming_plans',
                        'travel_health_insurance', 'travel_meds_vaccines', 'safety_gear', 'emergency_fund',
                        'luggage_locks', 'power_adapters', 'local_transport_cards', 'data_backups_cloud', 'other'
                    ]
                ]
            ]
        ];
    }
    
    public static function getCategoryDisplayName($key) {
        $names = [
            // Normal mode
            'car_purchase_loan' => 'Car purchase/loan',
            'car_insurance' => 'Car insurance',
            'fuel' => 'Fuel (gas/electric/hybrid)',
            'maintenance' => 'Maintenance (oil, tires, inspections)',
            'repairs' => 'Repairs',
            'parts_accessories' => 'Parts & accessories',
            // ... (add all category display names)
            'other' => 'Other'
        ];
        
        return $names[$key] ?? ucwords(str_replace('_', ' ', $key));
    }
}
