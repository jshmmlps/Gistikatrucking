<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class ReportModel extends Model
{
    protected $firebase;
    protected $reportsRef;

    public function __construct()
    {
        parent::__construct();
        // Get the Firebase Realtime Database instance from Services
        $this->firebase = Services::firebase();
        // Reports are stored under the "Reports" node
        $this->reportsRef = $this->firebase->getReference('Reports');
    }

    /**
     * Insert a new report record with an auto-incremented report number ("R000001", "R000002", etc.)
     *
     * @param array $data Data to insert.
     * @return string The generated report number.
     */
    public function insertReport(array $data)
    {
        // Retrieve existing reports from Firebase
        $reports = $this->reportsRef->getSnapshot()->getValue();
        $maxNumber = 0;
        if ($reports && is_array($reports)) {
            foreach ($reports as $key => $report) {
                if (isset($report['report_number'])) {
                    // Remove the "R" prefix and convert to int
                    $num = (int)substr($report['report_number'], 1);
                    if ($num > $maxNumber) {
                        $maxNumber = $num;
                    }
                }
            }
        }
        $newNumber = $maxNumber + 1;
        // Format the report number as "R" followed by 6 digits, e.g., R000001
        $reportNumber = 'R' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        $data['report_number'] = $reportNumber;
        // Set the report creation date
        $data['date'] = date('Y-m-d H:i:s');

        // Save the report in Firebase using the report number as key
        $this->reportsRef->getChild($reportNumber)->set($data);

        return $reportNumber;
    }

    /**
     * Update a report record by its report number.
     *
     * @param string $reportNumber
     * @param array $data
     * @return bool
     */
    public function updateReport($reportNumber, array $data)
    {
        $this->reportsRef->getChild($reportNumber)->update($data);
        return true;
    }
}
