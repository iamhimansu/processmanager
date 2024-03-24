# Process manager
- It manages multiple processes in php
- Example shown below
```php
// Data to process (replace with your data source)

$data = array_fill(0, $totalRecords, rand(1, 100));  // Sample data

// Split data into chunks
$chunks = array_chunk($data, $chunkSize);

$processManager = new ProcessManager();
$processManager->setProcessLimit(10);
foreach ($chunks as $i => $chunk) {
    $processWorker = new \app\processmanager\worker\Worker([
        'data' => $chunk,
    ]);
 $processManager->addToWaiting($processWorker);
}

// Wait for jobs to fininsh
 while ($processManager->hasJobs()) {
      echo "<pre>";
      var_dump("Waiting: {$processManager->getWaitingCount()}, Running: {$processManager->getRunningCount()}, Completed: {$processManager->getCompletedCount()}");
      echo "</pre>";
}
```
