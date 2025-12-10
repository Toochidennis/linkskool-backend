<?php

namespace V3\App\Services\Explore;

class TopicService
{
    /**
     * Dummy data containing topics for each course
     */
    private const COURSE_TOPICS = [
        '1' => [ // ENGLISH LANGUAGE
            'Parts of Speech',
            'Tenses and Verb Forms',
            'Subject-Verb Agreement',
            'Pronouns and Antecedents',
            'Active and Passive Voice',
            'Direct and Indirect Speech',
            'Clauses and Phrases',
            'Punctuation and Capitalization',
            'Comprehension and Summary',
            'Vocabulary and Word Formation',
            'Essay Writing Techniques',
            'Letter Writing',
        ],
        '2' => [ // LITERATURE IN ENGLISH
            'Introduction to Literary Genres',
            'Poetry Analysis and Appreciation',
            'Prose Fiction Analysis',
            'Drama and Theatre Studies',
            'African Literature',
            'European Literature',
            'Literary Devices and Techniques',
            'Character Analysis',
            'Theme and Plot Development',
            'Literary Criticism',
            'Oral Literature',
            'Contemporary Literature',
        ],
        '3' => [ // PHYSICS
            'Motion and Forces',
            'Energy and Work',
            'Electricity and Magnetism',
            'Waves and Optics',
            'Heat and Temperature',
            'Atomic and Nuclear Physics',
            'Electronics and Circuits',
            'Mechanics and Dynamics',
            'Simple Harmonic Motion',
            'Gravitational Fields',
            'Electrostatics',
            'Modern Physics',
        ],
        '4' => [ // GOVERNMENT
            'Introduction to Government',
            'Democracy and Its Features',
            'Constitution and Constitutionalism',
            'Separation of Powers',
            'Political Parties and Elections',
            'Federal System of Government',
            'Human Rights and Civil Liberties',
            'Public Administration',
            'International Relations',
            'Comparative Government',
            'Rule of Law and Judiciary',
            'Local Government Administration',
        ],
        '5' => [ // ECONOMICS
            'Basic Economic Concepts',
            'Demand and Supply',
            'Market Structures',
            'Theory of Production',
            'National Income Accounting',
            'Money and Banking',
            'Inflation and Deflation',
            'International Trade',
            'Public Finance',
            'Economic Development',
            'Fiscal and Monetary Policy',
            'Economic Systems',
        ],
        '6' => [ // AGRICULTURAL SCIENCES
            'Introduction to Agriculture',
            'Soil Science and Management',
            'Crop Production',
            'Animal Husbandry',
            'Farm Management',
            'Agricultural Economics',
            'Pest and Disease Control',
            'Agricultural Extension',
            'Forestry and Wildlife',
            'Fisheries Management',
            'Agricultural Mechanization',
            'Post-Harvest Technology',
        ],
        '7' => [ // CHEMISTRY
            'Atomic Structure',
            'Chemical Bonding',
            'States of Matter',
            'Chemical Reactions',
            'Acids, Bases and Salts',
            'Organic Chemistry',
            'Electrochemistry',
            'Chemical Equilibrium',
            'Rates of Reaction',
            'Periodic Table',
            'Oxidation and Reduction',
            'Environmental Chemistry',
        ],
        '8' => [ // Christian Religious Studies
            'The Bible and Its Authority',
            'Creation and Fall of Man',
            'The Patriarchs',
            'Moses and the Exodus',
            'The Prophets',
            'Life and Ministry of Jesus',
            'The Early Church',
            'Christian Ethics and Morality',
            'Christian Worship',
            'Christian Leadership',
            'Marriage and Family Life',
            'Contemporary Christian Issues',
        ],
        '9' => [ // MATHEMATICS
            'Number Systems',
            'Algebra and Linear Equations',
            'Quadratic Equations',
            'Geometry and Mensuration',
            'Trigonometry',
            'Statistics and Probability',
            'Sets and Logic',
            'Sequences and Series',
            'Differentiation',
            'Integration',
            'Vectors and Matrices',
            'Coordinate Geometry',
        ],
        '10' => [ // BIOLOGY
            'Cell Biology',
            'Classification of Living Things',
            'Nutrition and Digestion',
            'Respiration and Gas Exchange',
            'Transport Systems',
            'Excretion and Homeostasis',
            'Reproduction and Growth',
            'Genetics and Heredity',
            'Evolution and Adaptation',
            'Ecology and Environment',
            'Human Anatomy and Physiology',
            'Disease and Immunity',
        ],
        '11' => [ // GEOGRAPHY
            'Physical Geography',
            'Climate and Weather',
            'Vegetation and Soils',
            'Landforms and Geology',
            'Population Geography',
            'Settlement Patterns',
            'Economic Activities',
            'Map Reading and Interpretation',
            'Environmental Issues',
            'Resource Management',
            'Regional Geography',
            'Urban and Rural Development',
        ],
        '12' => [ // FINANCIAL ACCOUNTING
            'Introduction to Accounting',
            'Double Entry Bookkeeping',
            'Books of Original Entry',
            'Ledger and Trial Balance',
            'Financial Statements',
            'Bank Reconciliation',
            'Control Accounts',
            'Depreciation and Provisions',
            'Partnership Accounts',
            'Company Accounts',
            'Manufacturing Accounts',
            'Ratio Analysis',
        ],
        '13' => [ // ACCOUNTING
            'Accounting Principles and Concepts',
            'Recording Business Transactions',
            'Cash and Bank Transactions',
            'Credit Transactions',
            'Receivables and Payables',
            'Capital and Revenue Expenditure',
            'Final Accounts Preparation',
            'Correction of Errors',
            'Incomplete Records',
            'Non-Profit Organizations',
            'Cost Accounting',
            'Budgeting and Control',
        ],
        '15' => [ // COMMERCE
            'Introduction to Commerce',
            'Trade and Its Types',
            'Channels of Distribution',
            'Documents in Commerce',
            'Banking and Finance',
            'Insurance',
            'Transportation',
            'Warehousing and Storage',
            'Communication in Business',
            'Advertising and Marketing',
            'Consumer Protection',
            'E-Commerce and Technology',
        ],
        '16' => [ // VERBAL APTITUDE
            'Synonyms and Antonyms',
            'Analogies',
            'Sentence Completion',
            'Reading Comprehension',
            'Critical Reasoning',
            'Verbal Classification',
            'Idioms and Phrases',
            'Error Detection',
            'Para Jumbles',
            'Logical Deduction',
            'Vocabulary Building',
            'Word Association',
        ],
        '18' => [ // USE OF ENGLISH
            'Comprehension Skills',
            'Lexis and Structure',
            'Oral English',
            'Summary Writing',
            'Continuous Writing',
            'Register Usage',
            'Figures of Speech',
            'Sentence Patterns',
            'Grammatical Accuracy',
            'Paragraph Development',
            'Contextual Usage',
            'Discourse Analysis',
        ],
        '24' => [ // GENERAL PAPER
            'Current Affairs',
            'Science and Technology',
            'Politics and Governance',
            'Economic Issues',
            'Social Issues',
            'Environmental Concerns',
            'Arts and Culture',
            'Education and Development',
            'Health and Medicine',
            'International Relations',
            'Media and Communication',
            'Ethics and Philosophy',
        ],
        '25' => [ // IGBO
            'Mkpoputa Asusu Igbo',
            'Nkowa Okwu na Ahiriokwu',
            'Edemede Igbo',
            'Akuko Ifo na Omenala',
            'Uri na Abu Igbo',
            'Grammar Asusu Igbo',
            'Nkuzi Asusu',
            'Odinala na Omenaala',
            'Agumagu Igbo',
            'Mkpoputa Udaume',
            'Nchikota Edemede',
            'Mmuta Omenala',
        ],
        '26' => [ // Business Studies
            'Nature of Business',
            'Business Environment',
            'Forms of Business Ownership',
            'Business Resources',
            'Production and Operations',
            'Marketing Management',
            'Human Resource Management',
            'Financial Management',
            'Business Communication',
            'Entrepreneurship',
            'Business Ethics',
            'Business Planning',
        ],
        '27' => [ // CIVIC EDUCATION
            'Citizenship and Civic Rights',
            'National Identity and Values',
            'Democracy and Governance',
            'Rule of Law',
            'Human Rights',
            'Leadership and Followership',
            'Community Service',
            'Political Participation',
            'Social Responsibility',
            'Drug Abuse Prevention',
            'Traffic Regulations',
            'Environmental Protection',
        ],
        '29' => [ // HOME ECONOMICS
            'Nutrition and Diet',
            'Food Preparation and Cooking',
            'Textiles and Clothing',
            'Home Management',
            'Child Development',
            'Family Living',
            'Consumer Education',
            'Food Preservation',
            'Interior Decoration',
            'Laundry and Cleaning',
            'Health and Hygiene',
            'Food Service and Catering',
        ],
        '30' => [ // SOCIAL STUDIES
            'Culture and Society',
            'Family as a Social Unit',
            'Social Groups and Organizations',
            'Values and Norms',
            'Citizenship Education',
            'National Development',
            'Resources and Economic Activities',
            'Population and Migration',
            'Transportation and Communication',
            'Environmental Studies',
            'HIV/AIDS and Drug Education',
            'Peace and Conflict Resolution',
        ],
        '31' => [ // Further Maths
            'Advanced Algebra',
            'Complex Numbers',
            'Mathematical Induction',
            'Binomial Theorem',
            'Matrices and Determinants',
            'Vectors in 3D',
            'Advanced Calculus',
            'Differential Equations',
            'Analytical Geometry',
            'Mechanics',
            'Probability Theory',
            'Series and Sequences',
        ],
        '46' => [ // BASIC TECHNOLOGY
            'Introduction to Technology',
            'Engineering Drawing',
            'Woodwork and Metalwork',
            'Technical Drawing',
            'Basic Electronics',
            'Simple Machines',
            'Tools and Materials',
            'Safety in Workshop',
            'Measurement and Instrumentation',
            'Energy and Power',
            'Construction Technology',
            'Manufacturing Processes',
        ],
        '34' => [ // TRIVIA-English
            'General Knowledge',
            'Famous Personalities',
            'Historical Events',
            'World Geography Trivia',
            'Science Facts',
            'Sports Trivia',
            'Entertainment and Movies',
            'Literature Trivia',
            'Technology Trivia',
            'Music and Arts',
            'World Records',
            'Interesting Facts',
        ],
    ];

    public function __construct()
    {
        // Initialization if needed
    }

    /**
     * Get topics for a specific course by course ID
     *
     * @param string|int $courseId The course ID
     * @return array Array of topics for the course, or empty array if not found
     */
    public function getTopicsByCourseId($courseId): array
    {
        $courseId = (string) $courseId;

        return self::COURSE_TOPICS[$courseId] ?? [];
    }

    /**
     * Get all courses with their topics
     *
     * @return array All courses and their topics
     */
    public function getAllCourseTopics(): array
    {
        return self::COURSE_TOPICS;
    }

    /**
     * Check if topics exist for a course
     *
     * @param string|int $courseId The course ID
     * @return bool True if topics exist, false otherwise
     */
    public function hasTopics($courseId): bool
    {
        $courseId = (string) $courseId;

        return isset(self::COURSE_TOPICS[$courseId]) && !empty(self::COURSE_TOPICS[$courseId]);
    }

    /**
     * Get topic count for a specific course
     *
     * @param string|int $courseId The course ID
     * @return int Number of topics for the course
     */
    public function getTopicCount($courseId): int
    {
        return count($this->getTopicsByCourseId($courseId));
    }
}
