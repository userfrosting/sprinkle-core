<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

return [
    'ERROR' => [
        '@TRANSLATION' => 'Σφάλμα',

        '400' => [
            'TITLE'       => 'Σφάλμα 400: Bad Request',
            'DESCRIPTION' => 'Μάλλον δεν είναι δικό σου λάθος.',
        ],

        '404' => [
            'TITLE'       => 'Σφάλμα 404: Δεν βρέθηκε',
            'DESCRIPTION' => 'Δεν μπορούμε να βρούμε αυτό που ψάχνετε.',
            'DETAIL'      => 'Προσπαθήσαμε να βρούμε τη σελίδα σας ...',
            'EXPLAIN'     => 'Δεν βρήκαμε τη σελίδα που ψάχνατε.',
            'RETURN'      => 'Όπως κι αν είναι, κάντε κλικ <a href="{{url}}">εδώ</a> για να επιστρέψετε στην αρχική σελίδα.',
        ],

        'CONFIG' => [
            'TITLE'       => 'Πρόβλημα ρύθμισης παραμέτρων του UserFrosting!',
            'DESCRIPTION' => 'Ορισμένες απαιτήσεις διαμόρφωσης του UserFrosting δεν πληρούνται.',
            'DETAIL'      => 'Κάτι δεν είναι εδώ.',
            'RETURN'      => 'Παρακαλούμε διορθώστε τα ακόλουθα σφάλματα και στη συνέχεια, <a href="{{url}}">επαναλάβετε τη φόρτωση</a>.',
        ],

        'DESCRIPTION' => 'Έχουμε αισθανθεί μια μεγάλη διαταραχή στη Δύναμη.',
        'DETAIL'      => 'Λοιπόν έχουμε αυτά τα στοιχεία:',

        'ENCOUNTERED' => 'Εεεε ... κάτι συνέβη, δεν ξέρουμε τι.',

        'MAIL' => 'Κρίσιμο σφάλμα κατά την προσπάθεια αποστολής ηλ. μηνύματος, επικοινωνήστε με τον διαχειριστή του διακομιστή σας. Αν είστε ο διαχειριστής, ελέγξτε το αρχείο καταγραφής UserFrosting.',

        'RETURN' => 'Κάντε κλικ <a href="{{url}}"> εδώ </a> για να επιστρέψετε στην αρχική σελίδα.',

        'SERVER' => 'Ωχ, φαίνεται ότι ο διακομιστής μας μπορεί να έχει μπερδευτεί. Αν είστε ο διαχειριστής, ελέγξτε τα αρχεία καταγραφής PHP ή UserFrosting.',

        'TITLE' => 'Διαταραχή της Δύναμης',
    ],
];
