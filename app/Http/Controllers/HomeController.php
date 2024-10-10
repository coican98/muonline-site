<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use Session;
use File;
use Carbon;

class HomeController extends Controller
{
    public function downloads()
    {
        $title = 'Mu Rootz - Downloads';
        $files = File::allFiles(storage_path('app\public\download'));

        $downloads = [];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $size = $file->getSize();
                $formattedSize = $this->formatSizeUnits($size);
                $downloads[] = [
                    'name' => $file->getBasename(),
                    'link' => '/download/' . $file->getFilename(),
                    'size' => $formattedSize,
                ];
            }
        }

        return view('downloads', ['downloads' => $downloads],compact('title'));
    }
    public function getEventSchedule()
    {
    $eventsFolderPath = 'D:\Downloads\Mu Files\MuServerS6\MuServer_Season_6_Update_19\Data\Event';
    $invasionManagerPath = $eventsFolderPath.'\InvasionManager.dat';
    $lines = file($invasionManagerPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $events = [
        0 => "Skeleton King",
        1 => "Red Dragon",
        2 => "Golden Dragon",
        3 => "White Wizard",
        4 => "Ano Novo",
        5 => "Coelhos",
        6 => "Verão",
        7 => "Natal",
        8 => "Medusa",
        9 => "Hydra",
        10 => "Erohim",
        11 => "Zaikan",
        12 => "Custom 1", // No corresponding line, could be added if necessary
        13 => "Narcondra",
        14 => "Grand Wizard",
        15 => "Cavalry Captain",
        16 => "Quartermaster",
        17 => "Combat Instructor",
        18 => "Knight Commander",
        19 => "Master Assassin",
        20 => "Kundun K7"
    ];
    $timestamps = [];
    $enabledEvents = [];
    foreach ($events as $index => $event) {
        if (!isset($enabledEvents[$index])) {
            $enabledEvents[$index] = [
                'enabled' => false
            ];
        }
        if (!isset($timestamps[$index])) {
            $timestamps[$index] = [
                null
            ];
        }
    }
    $inSection0 = false;
    $inSection1 = false;
    $daysOfWeek = ['*','Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

    foreach ($lines as $index => $line) {
        $trimmedLine = trim($line);
        if (preg_match('/^\d+$/', $trimmedLine)) {
            $currentSection = intval($trimmedLine); // Set the current section
            continue;
        }
        if ($trimmedLine === 'end') {
            $currentSection = null; // Reset section tracking
            continue;
        }
        if (strpos($trimmedLine, '//') === 0) {
            continue;
        }
        if ($currentSection === 0) {
            $parts = preg_split('/\s+/', $trimmedLine);

            if (count($parts) >= 8) {
                $index = $parts[0];
                $year = $parts[1];
                $month = $parts[2];
                $day = $parts[3];
                $dow = $parts[4];
                $hour = $parts[5];
                $minute = $parts[6];
                $second = $parts[7];

                if (!is_numeric($index)) {
                    continue;
                }

                $timestamps[$index] = [
                    'index' => $index,
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'dow' => $daysOfWeek[$dow] ?? '*',
                    'hour' => $hour,
                    'minute' => $minute,
                    'second' => $second
                ];
            }
        }
        if ($currentSection === 1) {
            $parts = preg_split('/\s+/', $trimmedLine);
            $eventIndex = intval($parts[0]); // The first column is the index
        
            // Check if the event index exists in $events
            if (isset($events[$eventIndex])) {
                $enabledEvents[$eventIndex] = [
                    'enabled' => true
                ];
            }
        }
    }
    $eventData = [];
    foreach ($events as $index => $event) {
        $enabledValue = $enabledEvents[$index];
        $isEnabled = $enabledValue['enabled'];
        $timestamp = $timestamps[$index];
        if(isset($timestamp['dow'])){
            $ddow = $timestamp['dow'];
        }
        $eventData[] = [
            'name' => $events[$index] ?? 'Unknown Event',
            'timestamp' => $timestamp,
            'enabled' => $isEnabled,
            'dow' => $ddow
        ];
    }
    // dd($eventData);
    return $eventData;
    }
    public function loadEvents(){
        $eventData = $this->getEvents();
        // dd($eventData);
        return view('partials.events', [
            'eventData' => $eventData,
        ]);
    }
    public function getEvents() {
        $schedule = $this->getEventSchedule();
        $data = [];
        $now = now()->toArray();
    
        foreach ($schedule as $event) {
            if (isset($event['name'])) {
                if (isset($event['timestamp']) && isset($event['timestamp']['hour']) && isset($event['timestamp']['minute'])) {
                    $hour = $event['timestamp']['hour'];
                    $minute = $event['timestamp']['minute'];
    
                    if ($hour === '*') {
                        if ($minute > $now['minute']) {
                            $hour = $now['hour'];
                        } else {
                            $hour = ($now['hour'] + 1) % 24;
                        }
                    }
    
                    $hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
                    $minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
    
                    $data[] = [
                        'event' => $event['name'],
                        'time' => $hour . ':' . $minute,
                        'enabled' => $event['enabled'],
                        'dow' => $event['dow']
                    ];
                } else {
                    $data[] = [
                        'event' => $event['name'],
                        'time' => null,
                        'enabled' => false,
                    ];
                }
            }
        }
        return $data;
    }
    
    public function index()
    {
        $title = 'Mu Rootz - Home';
        
        $this->loadEvents();

        $CSOwner = DB::table('MuCastle_DATA')->value('OWNER_GUILD');

        return view('home', [
            'title' => $title,
            'CSOwner' => $CSOwner
        ]);

    }

    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}