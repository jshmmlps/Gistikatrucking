<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;

/**
 * A controller purely for scanning "report_images/" in Firebase Storage.
 * 
 * You typically won't visit this in a browser; you'll call it
 * from AdminController or via Cron if desired.
 */
class StorageScan extends Controller
{
    /**
     * Main method to scan the Firebase Storage "report_images" folder
     * and insert new "Reports" in the Realtime Database if not already present.
     */
    public function index()
    {
        // 1) Get Firebase Storage bucket
        $storage     = Services::firebaseStorage();  // from your services.php
        $bucketName  = env('FIREBASE_STORAGE_BUCKET'); // e.g. "your-app.appspot.com"
        $bucket      = $storage->getBucket($bucketName);

        // 2) List all objects under "report_images/"
        $objects = $bucket->objects([
            'prefix' => 'report_images/', 
        ]);

        // We'll need our ReportModel to insert new reports
        $reportModel = new \App\Models\ReportModel();

        // 3) Loop through all found objects
        foreach ($objects as $object) {
            $fullPath = $object->name(); 
            // e.g. "report_images/Delivery/Driver1_1742938527362.jpg"

            // Skip if it’s a "folder placeholder" (some buckets store an empty directory object)
            if (substr($fullPath, -1) === '/') {
                continue;
            }

            // We expect the path structure: report_images/<folder>/<filename>
            // e.g. "report_images/Delivery/Driver1_1742938527362.jpg"
            $pathParts = explode('/', $fullPath);
            // pathParts[0] = "report_images"
            // pathParts[1] = "Delivery" (folder)
            // pathParts[2] = "Driver1_1742938527362.jpg"
            if (count($pathParts) < 3) {
                // Not a valid path structure
                continue;
            }

            // The subfolder: "Delivery" or "Discrepancy"
            $folder   = $pathParts[1]; 
            $fileName = $pathParts[2]; // e.g. "Driver1_1742938527362.jpg"

            // Parse the filename by underscore: "Driver1_1742938527362.jpg" => ["Driver1", "1742938527362.jpg"]
            if (!strpos($fileName, '_')) {
                continue;
            }

            [$driverId, $timestampPart] = explode('_', $fileName);
            if (empty($driverId) || empty($timestampPart)) {
                continue;
            }

            // Remove file extension from the timestamp (e.g. "1742938527362.jpg" => "1742938527362")
            $timestampOnly = explode('.', $timestampPart)[0];
            if (!ctype_digit($timestampOnly)) {
                // If it's not numeric, skip
                continue;
            }

            // Convert the timestamp (assuming milliseconds):
            $ts = (int)$timestampOnly;
            $dateStr = date('Y-m-d H:i:s', $ts / 1000);

            // Build a public download URL
            $encodedPath = urlencode($fullPath);
            $imgUrl = "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o/{$encodedPath}?alt=media";

            // 4) Check if we already have a report with this exact img_url
            if ($this->reportExistsByImage($imgUrl)) {
                // Skip it, already processed
                continue;
            }

            // 5) Find the booking for this driver (using the structure you provided in Bookings).
            //    If you want to pick the first booking that matches driver_id, do so:
            $bookingId = $this->findBookingForDriver($driverId);

            // 6) Determine the "report_type" from the folder
            $reportType = ($folder === 'Delivery') 
                ? 'Delivery Report'
                : 'Discrepancy Report';

            // 7) Insert the report using our existing model
            //    insertReport() will auto-generate the next "R000001", set the date automatically, etc.
            //    But we can override the date with the actual $dateStr by updating it after.
            $data = [
                'report_type' => $reportType,
                'booking_id'  => $bookingId,
                'user_id'     => '',  // blank if it comes from driver (per your requirement)
            ];
            $reportNumber = $reportModel->insertReport($data);

            // 8) Update the newly created report with our actual image URL and date
            $reportModel->updateReport($reportNumber, [
                'img_url' => $imgUrl,
                'date'    => $dateStr,
            ]);
        }
    }

    /**
     * Check if a "Reports" entry already has this img_url.
     */
    private function reportExistsByImage($imgUrl): bool
    {
        $db = Services::firebase();
        $reportsRef = $db->getReference('Reports');
        $snapshot = $reportsRef->getSnapshot();
        $reports = $snapshot->getValue() ?? [];

        if (!is_array($reports)) {
            return false;
        }

        foreach ($reports as $report) {
            if (isset($report['img_url']) && $report['img_url'] === $imgUrl) {
                return true;
            }
        }

        return false;
    }

    /**
     * Given the driverId (e.g. "Driver2"), find the first booking in "Bookings"
     * that has bookingData['driver_id'] == driverId.
     * 
     * You mentioned you store them like:
     * 
     * Bookings
     *   3:
     *     booking_id: 3
     *     driver_id: "Driver2"
     *     ...
     * 
     * We'll return whatever is stored in bookingData['booking_id'] (the numeric ID).
     * If no booking is found for this driver, return ''.
     */
    private function findBookingForDriver($driverId)
    {
        $db = Services::firebase();
        $bookingsRef = $db->getReference('Bookings');
        $snapshot = $bookingsRef->getSnapshot();
        $allBookings = $snapshot->getValue() ?? [];

        if (!is_array($allBookings)) {
            return '';
        }

        foreach ($allBookings as $bookingKey => $bookingData) {
            if (
                isset($bookingData['driver_id']) && 
                $bookingData['driver_id'] === $driverId
            ) {
                // Return the booking_id field inside this booking
                // e.g. if bookingData['booking_id'] = 3
                return $bookingData['booking_id'] ?? '';
            }
        }

        return '';
    }
}
