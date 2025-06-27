<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;

class StorageScan extends Controller
{
    public function index()
    {
        // 1) Get Firebase services
        $storage = Services::firebaseStorage();
        $db = Services::firebase();
        $bucketName = env('FIREBASE_STORAGE_BUCKET');
        $bucket = $storage->getBucket($bucketName);

        // 2) Calculate cutoff time (3 months ago in milliseconds)
        $threeMonthsAgo = strtotime('-3 months') * 1000;

        // 3) First, clean up old reports from database
        $this->deleteOldReports($threeMonthsAgo);

        // 4) Process new reports
        $objects = $bucket->objects(['prefix' => 'report_images/']);
        $reportModel = new \App\Models\ReportModel();

        foreach ($objects as $object) {
            $fullPath = $object->name();
            
            // Skip folders and invalid paths
            if (substr($fullPath, -1) === '/' || count(explode('/', $fullPath)) < 3) {
                continue;
            }

            // Parse filename
            $pathParts = explode('/', $fullPath);
            $fileName = $pathParts[2];
            
            if (!strpos($fileName, '_')) {
                continue;
            }

            [$driverId, $timestampPart] = explode('_', $fileName);
            $timestampOnly = explode('.', $timestampPart)[0];
            
            if (empty($driverId) || empty($timestampOnly) || !ctype_digit($timestampOnly)) {
                continue;
            }

            $ts = (int)$timestampOnly;
            
            // Skip if older than 3 months
            if ($ts < $threeMonthsAgo) {
                continue;
            }

            // Generate image URL
            $encodedPath = urlencode($fullPath);
            $imgUrl = "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o/{$encodedPath}?alt=media";

            // Skip if already exists
            if ($this->reportExistsByImage($imgUrl)) {
                continue;
            }

            // Process new report
            $folder = $pathParts[1];
            $bookingId = $this->findBookingForDriver($driverId);
            $dateStr = date('Y-m-d H:i:s', $ts / 1000);
            
            $reportType = ($folder === 'Delivery') ? 'Delivery Report' : 'Discrepancy Report';
            
            $data = [
                'report_type' => $reportType,
                'booking_id' => $bookingId,
                'user_id' => '',
                'img_url' => $imgUrl,
                'date' => $dateStr,
                'timestamp' => $ts // Store timestamp for future cleanup
            ];
            
            $reportModel->insertReport($data);
        }
    }

    /**
     * Delete reports older than the specified timestamp
     */
   private function deleteOldReports($cutoffTimestamp)
    {
        $db = Services::firebase();
        $reportsRef = $db->getReference('Reports');
        $reports = $reportsRef->getValue() ?? [];

        foreach ($reports as $reportKey => $report) {
            if (isset($report['date'])) {
                // Convert report date to timestamp (in seconds)
                $reportTimestamp = strtotime($report['date']);
                
                // Compare in seconds (remove *1000 if your cutoff is in seconds)
                if ($reportTimestamp < ($cutoffTimestamp / 1000)) {
                    $reportsRef->getChild($reportKey)->remove();
                    
                    // Optional: Uncomment to delete from storage too
                    // if (isset($report['img_url'])) {
                    //     $this->deleteStorageFile($report['img_url']);
                    // }
                }
            }
        }
    }

    /**
     * Optional: Delete the actual file from Firebase Storage
     */
    private function deleteStorageFile($imgUrl)
    {
        $storage = Services::firebaseStorage();
        $bucket = $storage->getBucket(env('FIREBASE_STORAGE_BUCKET'));
        
        // Extract path from URL
        $pattern = '/o\/(report_images\/.*?)\?alt=media/';
        preg_match($pattern, $imgUrl, $matches);
        
        if (isset($matches[1])) {
            $filePath = urldecode($matches[1]);
            $object = $bucket->object($filePath);
            if ($object->exists()) {
                $object->delete();
            }
        }
    }

    private function reportExistsByImage($imgUrl): bool
    {
        $db = Services::firebase();
        $reportsRef = $db->getReference('Reports');
        $snapshot = $reportsRef->getSnapshot();
        $reports = $snapshot->getValue() ?? [];

        foreach ($reports as $report) {
            if (isset($report['img_url']) && $report['img_url'] === $imgUrl) {
                return true;
            }
        }
        return false;
    }

    private function findBookingForDriver($driverId)
    {
        $db = Services::firebase();
        $bookingsRef = $db->getReference('Bookings');
        $snapshot = $bookingsRef->getSnapshot();
        $allBookings = $snapshot->getValue() ?? [];

        foreach ($allBookings as $bookingData) {
            if (isset($bookingData['driver_id']) && $bookingData['driver_id'] === $driverId) {
                return $bookingData['booking_id'] ?? '';
            }
        }
        return '';
    }
}