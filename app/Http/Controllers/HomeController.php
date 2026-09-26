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
        $files = \File::allFiles(storage_path('app\public\download'));
        $externalFiles = DB::table('external_downloads')->get();
        
        $downloads = [];
        
        if ($externalFiles) {
            foreach ($externalFiles as $externalFile) {
                $downloads[] = [
                    'name' => $externalFile->name,
                    'link' => $externalFile->link,
                    'size' => $externalFile->size,
                    'date' => \Carbon\Carbon::parse($externalFile->created_at)->timestamp
                ];
            }
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                $size = $file->getSize();
                $formattedSize = $this->formatSizeUnits($size);
                $time = $file->getMTime();
                $downloads[] = [
                    'name' => $file->getBasename(),
                    'link' => '/download/' . $file->getFilename(),
                    'size' => $formattedSize,
                    'date' => $time
                ];
            }
        }
        
        usort($downloads, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return view('downloads', ['downloads' => $downloads], compact('title'));
    }

    public function eventsPage()
    {
        $title = 'Mu Rootz - Detalhes de Eventos';
        $eventData = \App\Services\EventScheduleService::getAllEvents();
        $isAdmin = Auth::check() && Auth::user()->global_admin == 1;
        
        // Filtra eventos: Admin vê todos, jogador vê apenas ativos
        $filteredEvents = [];
        foreach ($eventData as $event) {
            if ($isAdmin || $event['enabled']) {
                // Expande e ordena todos os horários cronologicamente (ex: mesclando *:18 com 14:05 e 22:05)
                $allTimeList = [];
                if (!empty($event['all_hours'])) {
                    foreach ($event['all_hours'] as $hStr) {
                        if (str_starts_with($hStr, '*:')) {
                            $min = substr($hStr, 2);
                            for ($h = 0; $h < 24; $h++) {
                                $allTimeList[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . $min;
                            }
                        } else {
                            $allTimeList[] = $hStr;
                        }
                    }
                } elseif (isset($event['timestamp']['hour']) && $event['timestamp']['hour'] === '*') {
                    $min = str_pad($event['timestamp']['minute'] ?? '00', 2, '0', STR_PAD_LEFT);
                    for ($h = 0; $h < 24; $h++) {
                        $allTimeList[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . $min;
                    }
                } elseif (isset($event['timestamp']['hour'])) {
                    $allTimeList[] = str_pad($event['timestamp']['hour'], 2, '0', STR_PAD_LEFT) . ':' . str_pad($event['timestamp']['minute'] ?? '00', 2, '0', STR_PAD_LEFT);
                }

                $allTimeList = array_values(array_unique($allTimeList));
                sort($allTimeList, SORT_STRING); // Ordenação cronológica estrita
                $event['all_hours_sorted'] = $allTimeList;

                $filteredEvents[] = $event;
            }
        }

        // Ordena os eventos: Ativos primeiro, Inativos no final. Dentro de cada grupo, em ordem alfabética (A-Z)
        usort($filteredEvents, function($a, $b) {
            $aEnabled = !empty($a['enabled']);
            $bEnabled = !empty($b['enabled']);

            if ($aEnabled !== $bEnabled) {
                return $aEnabled ? -1 : 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });
        
        return view('events_details', [
            'events' => $filteredEvents,
            'title' => $title,
            'isAdmin' => $isAdmin
        ]);
    }

    public function getEventSchedule()
    {
        return \App\Services\EventScheduleService::getAllEvents();
    }
    public function loadEvents()
    {
        $eventData = $this->getEvents();
        $isAdmin = Auth::check() && Auth::user()->global_admin == 1;

        // Filtra para exibição no menu lateral: Admin vê todos, jogadores vêem apenas os ativos com horário
        $displayEvents = [];
        foreach ($eventData as $item) {
            if ($isAdmin || ($item['enabled'] && $item['time'] !== null)) {
                $displayEvents[] = $item;
            }
        }
        
        // Ordena por: primeiro os que têm tempo calculado menor até abrir; inativos/sem data ao final
        usort($displayEvents, function($a, $b) {
            if ($a['enabled'] !== $b['enabled']) {
                return $a['enabled'] ? -1 : 1;
            }
            if ($a['seconds_until'] === null && $b['seconds_until'] === null) return 0;
            if ($a['seconds_until'] === null) return 1;
            if ($b['seconds_until'] === null) return -1;
            return $a['seconds_until'] <=> $b['seconds_until'];
        });
        
        return view('partials.events', [
            'eventData' => $displayEvents,
        ]);
    }

    public function getEvents()
    {
        $schedule = $this->getEventSchedule();
        $data = [];
        $now = now();
        $currentDow = $now->dayOfWeek; // 0 = Domingo, 1 = Segunda, ..., 6 = Sábado

        $dayMap = [
            'domingo' => 0,
            'segunda-feira' => 1,
            'terça-feira' => 2,
            'quarta-feira' => 3,
            'quinta-feira' => 4,
            'sexta-feira' => 5,
            'sábado' => 6,
        ];

        foreach ($schedule as $event) {
            if (!isset($event['name'])) {
                continue;
            }

            if (isset($event['timestamp']) && isset($event['timestamp']['hour']) && isset($event['timestamp']['minute'])) {
                $hour = $event['timestamp']['hour'];
                $minute = $event['timestamp']['minute'];
                $dow = $event['dow'] ?? '*';

                if ($hour === '*') {
                    if ((int)$minute > $now->minute) {
                        $hour = $now->hour;
                    } else {
                        $hour = ($now->hour + 1) % 24;
                    }
                }

                $h = (int) $hour;
                $m = (int) $minute;
                $formattedTime = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);

                // Identifica todos os dias e horários possíveis configurados para este evento
                $candidatePairs = [];
                if (!empty($event['all_schedules'])) {
                    foreach ($event['all_schedules'] as $sch) {
                        $candidatePairs[] = [
                            'dow' => $sch['dow'] ?? '*',
                            'hour' => $sch['hour'] ?? '*',
                            'minute' => $sch['minute'] ?? '0',
                        ];
                    }
                } else {
                    $candidateHours = !empty($event['all_hours']) ? $event['all_hours'] : [$formattedTime];
                    foreach ($candidateHours as $candTime) {
                        $candParts = explode(':', $candTime);
                        if (count($candParts) === 2) {
                            $candidatePairs[] = [
                                'dow' => $dow,
                                'hour' => $candParts[0],
                                'minute' => $candParts[1],
                            ];
                        }
                    }
                }

                $bestSecondsUntil = null;
                $bestTimeFormatted = $formattedTime;

                foreach ($candidatePairs as $pair) {
                    $candH = $pair['hour'];
                    $candM = (int) $pair['minute'];
                    $pairDow = $pair['dow'];

                    if ($candH === '*') {
                        if ($candM > $now->minute) {
                            $candH = $now->hour;
                        } else {
                            $candH = ($now->hour + 1) % 24;
                        }
                    } else {
                        $candH = (int) $candH;
                    }

                    // Pode ter múltiplos dias se pairDow for separado por vírgula
                    $dowList = array_map('trim', explode(',', $pairDow));
                    foreach ($dowList as $singleDow) {
                        $candTarget = $now->copy()->setTime($candH, $candM, 0);
                        $cleanDow = strtolower(trim((string)$singleDow));

                        if (isset($dayMap[$cleanDow])) {
                            $targetDow = $dayMap[$cleanDow];
                            $daysAhead = ($targetDow - $currentDow + 7) % 7;
                            if ($daysAhead > 0) {
                                $candTarget->addDays($daysAhead);
                            } elseif ($candTarget->isPast()) {
                                $candTarget->addDays(7);
                            }
                        } else {
                            if ($candTarget->isPast()) {
                                $candTarget->addDay();
                            }
                        }

                        $diffSec = $now->diffInSeconds($candTarget, false);
                        if ($diffSec < 0) $diffSec = 0;

                        if ($bestSecondsUntil === null || $diffSec < $bestSecondsUntil) {
                            $bestSecondsUntil = $diffSec;
                            $bestTimeFormatted = str_pad($candH, 2, '0', STR_PAD_LEFT) . ':' . str_pad($candM, 2, '0', STR_PAD_LEFT);
                        }
                    }
                }

                $data[] = [
                    'event' => $event['name'],
                    'time' => $bestTimeFormatted,
                    'enabled' => !empty($event['enabled']),
                    'dow' => $dow,
                    'category' => $event['category'] ?? 'Evento',
                    'seconds_until' => $bestSecondsUntil,
                ];
            } else {
                $data[] = [
                    'event' => $event['name'],
                    'time' => null,
                    'enabled' => !empty($event['enabled']),
                    'dow' => '*',
                    'category' => $event['category'] ?? 'Evento',
                    'seconds_until' => null,
                ];
            }
        }
        return $data;
    }

    public function index()
    {
        $title = 'Mu Rootz - Home';

        $this->loadEvents();

        $CSOwner = DB::table('MuCastle_DATA')->value('OWNER_GUILD');

        // Carrega notícias dinâmicas e notícia destacada no modal da página inicial
        $recentNews = \App\Services\NewsService::getRecentForHome(3);
        $homeModalNews = \App\Services\NewsService::getHomeModalNews();

        return view('home', [
            'title' => $title,
            'CSOwner' => $CSOwner,
            'recentNews' => $recentNews,
            'homeModalNews' => $homeModalNews,
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