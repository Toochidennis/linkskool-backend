<?php

namespace V3\App\Services\Explore;

class AdmissionService
{
    public function __construct()
    {
    }

    public function getAdmissionInfo(): array
    {
        return  [
            'near_me' => [
                [
                    'id' => 1,
                    'school_name' => 'Daughters of Divine Love Secondary School, Enugu',
                    'is_admission' => true,
                    "rating" => 4.7,
                    'start_date' => '2025-12-01',
                    'end_date' => '2026-01-31',
                    'contact' => [
                        'phone' => '+2348030000001',
                        'email' => 'admissions@ddlse.ng',
                    ],
                    'admission_price' => 15000,
                    'location' => 'Opposite Holy Rosary, New Haven, Enugu',
                    'address' => 'No. 12 Chapel Road, New Haven, Enugu State',
                    'latitude' => 6.4531,
                    'longitude' => 7.4951,
                    'motto' => 'Faith, Knowledge, Service',
                    'school_type' => 'Secondary (Girls)',
                    'about' => 'A faith-based secondary school with strong academics and discipline. Offers boarding and day options.',
                    'gallery' => [
                        'https://example.com/images/ddlse-main-hall.jpg',
                        'https://example.com/images/ddlse-science.jpg',
                    ],
                    'banner' => 'https://example.com/images/ddlse-banner.jpg',
                    'logo' => 'https://example.com/images/ddlse-logo.png',
                    'testimonials' => [
                        [
                            'id' => 101,
                            'name' => 'Mrs. A. Okoye',
                            'content' => 'Excellent teachers and safe environment. My daughter improved her grades in one term.',
                            'rating' => 4.8,
                            'date' => '2024-09-12',
                        ],
                    ],
                ],
                [
                    'id' => 2,
                    'school_name' => 'Winsschool International Academy, Enugu',
                    'is_admission' => false,
                    'rating' => 4.3,
                    'contact' => [
                        'phone' => '+2348030000002',
                        'email' => 'info@winsschool.ng',
                    ],
                    'admission_price' => 25000,
                    'location' => 'Independence Layout, Enugu',
                    'address' => 'Plot 45, Independence Layout, Enugu State',
                    'latitude' => 6.4410,
                    'longitude' => 7.4940,
                    'motto' => 'Excellence Through Integrity',
                    'school_type' => 'Secondary (Co-ed)',
                    'about' => 'Private international curriculum school with strong extra-curricular programs.',
                    'gallery' => [
                        'https://example.com/images/winsschool-front.jpg',
                        'https://example.com/images/winsschool-sports.jpg',
                    ],
                    'banner' => 'https://example.com/images/winsschool-banner.jpg',
                    'logo' => 'https://example.com/images/winsschool-logo.png',
                    'testimonials' => [
                        [
                            'id' => 102,
                            'name' => 'Mr. C. Nwafor',
                            'content' => 'Great blend of academics and sports. Teachers are engaged and responsive.',
                            'rating' => 4.6,
                            'date' => '2025-02-05',
                        ],
                    ],
                ],
            ],

            'recommend' => [
                [
                    'id' => 4,
                    'school_name' => 'St. Theresa\'s College, Enugu',
                    'is_admission' => false,
                    'rating' => 4.1,
                    'contact' => [
                        'phone' => '+2348030000004',
                        'email' => 'hello@sttheresa-en.ng',
                    ],
                    'admission_price' => 18000,
                    'location' => 'GRA, Enugu',
                    'address' => '12 Bishop Nwankwo Street, GRA, Enugu',
                    'latitude' => 6.4498,
                    'longitude' => 7.4925,
                    'motto' => 'Knowledge and Virtue',
                    'school_type' => 'Secondary (Girls)',
                    'about' => 'Well-regarded girls\' secondary school with strong arts and humanities programs.',
                    'gallery' => [
                        'https://example.com/images/sttheresa-library.jpg',
                    ],
                    'banner' => 'https://example.com/images/sttheresa-banner.jpg',
                    'logo' => 'https://example.com/images/sttheresa-logo.png',
                    'testimonials' => [
                        [
                            'id' => 104,
                            'name' => 'Pastor U.',
                            'content' => 'Teachers empower students. Good discipline policies.',
                            'rating' => 4.5,
                            'date' => '2023-11-02',
                        ],
                    ],
                ],
                [
                    'id' => 5,
                    'school_name' => 'Queen\'s International School, Enugu',
                    'is_admission' => true,
                    'rating' => 4.5,
                    'start_date' => '2025-12-10',
                    'end_date' => '2026-02-10',
                    'contact' => [
                        'phone' => '+2348030000005',
                        'email' => 'admissions@queens-intl.ng',
                    ],
                    'admission_price' => 30000,
                    'location' => 'New Haven',
                    'address' => 'Plot 8, New Haven District, Enugu',
                    'latitude' => 6.4538,
                    'longitude' => 7.4934,
                    'motto' => 'Lead with Purpose',
                    'school_type' => 'Secondary (Co-ed, International)',
                    'about' => 'International curriculum with focus on STEM and leadership programs.',
                    'gallery' => [
                        'https://example.com/images/queens-stem.jpg',
                    ],
                    'banner' => 'https://example.com/images/queens-banner.jpg',
                    'logo' => 'https://example.com/images/queens-logo.png',
                    'testimonials' => [
                        [
                            'id' => 105,
                            'name' => 'Dr. K. Eze',
                            'content' => 'Cutting-edge labs and leadership training. Worth the fee.',
                            'rating' => 4.9,
                            'date' => '2025-03-18',
                        ],
                    ],
                ],
            ],

            'top' => [
                [
                    'id' => 7,
                    'school_name' => 'Enugu International Academy',
                    'is_admission' => true,
                    'rating' => 4.0,
                    'start_date' => '2025-11-01',
                    'end_date' => '2026-01-15',
                    'contact' => [
                        'phone' => '+2348030000007',
                        'email' => 'admissions@enugu-intl.ng',
                    ],
                    'admission_price' => 45000,
                    'location' => 'Independence Layout',
                    'address' => '1 International Drive, Independence Layout, Enugu',
                    'latitude' => 6.4425,
                    'longitude' => 7.4972,
                    'motto' => 'Global Minds, Local Roots',
                    'school_type' => 'Secondary (Co-ed, International)',
                    'about' => 'Top-tier international school offering IB-style curriculum and university counseling.',
                    'gallery' => [
                        'https://example.com/images/enuguia-aerial.jpg',
                        'https://example.com/images/enuguia-arts.jpg',
                    ],
                    'banner' => 'https://example.com/images/enuguia-banner.jpg',
                    'logo' => 'https://example.com/images/enuguia-logo.png',
                    'testimonials' => [
                        [
                            'id' => 107,
                            'name' => 'Mrs. E. Nwosu',
                            'content' => 'Exceptional university placements and well-rounded education.',
                            'rating' => 4.95,
                            'date' => '2025-05-27',
                        ],
                    ],
                ],
            ],
        ];
    }
}
