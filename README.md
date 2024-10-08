# Process manager


Performs multi processing in PHP, useful for processing huge datasets.

Datasets are divided into chunks / children's, and each chunk is passed and executed into it own isolated shell, thus achieving parallel processing.

After the execution, each child informs its completion state to its parent, and the parent intelligently pushes new children's to the queue for processing next chunk.


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
