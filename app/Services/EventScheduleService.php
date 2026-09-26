<?php

namespace App\Services;

class EventScheduleService
{
    /**
     * Mapeamento de dias da semana (Padrão MuOnline / X-Team)
     * 1 = Domingo, 2 = Segunda, 3 = Terça, 4 = Quarta, 5 = Quinta, 6 = Sexta, 7 = Sábado
     * (E 0 também aceito como Domingo caso algum arquivo legado use 0)
     */
    public static array $daysOfWeek = [
        '*' => '*',
        '-1' => '*',
        '0' => 'Domingo',
        '1' => 'Domingo',
        '2' => 'Segunda-feira',
        '3' => 'Terça-feira',
        '4' => 'Quarta-feira',
        '5' => 'Quinta-feira',
        '6' => 'Sexta-feira',
        '7' => 'Sábado',
    ];

    /**
     * Mapeamento estrito para padrão 1-7 (onde 1=Dom, 2=Seg, 3=Ter, 4=Qua, 5=Qui, 6=Sex, 7=Sáb)
     */
    public static array $daysOfWeek1To7 = [
        '1' => 'Domingo',
        '2' => 'Segunda-feira',
        '3' => 'Terça-feira',
        '4' => 'Quarta-feira',
        '5' => 'Quinta-feira',
        '6' => 'Sexta-feira',
        '7' => 'Sábado',
    ];

    /**
     * Retorna a lista completa de todos os eventos configurados no MuServer
     */
    public static function getAllEvents(): array
    {
        $baseMuServer = env('MUSERVER_LOCALE', 'C:\\MuServer\\');
        $baseMuServer = rtrim($baseMuServer, '\\/') . '\\';

        $allEvents = [];

        // 1. Invasões (InvasionManager.dat)
        $invasions = self::parseInvasionManager($baseMuServer . 'Data\\Event\\InvasionManager.dat');
        foreach ($invasions as $inv) {
            $allEvents[] = $inv;
        }

        // 2. Eventos Padrões em C:\MuServer\Data\Event\*.dat
        $standardEvents = self::parseStandardEventFolder($baseMuServer . 'Data\\Event');
        foreach ($standardEvents as $evt) {
            $allEvents[] = $evt;
        }

        // 3. Eventos Customizados em C:\MuServer\Data\Custom\*.txt
        $customEvents = self::parseCustomFolder($baseMuServer . 'Data\\Custom');
        foreach ($customEvents as $evt) {
            $allEvents[] = $evt;
        }

        // 4. Configuração Geral de Ativação do GameServer (GameServerInfo - Event.dat)
        $gsSwitch = self::parseGameServerEventSwitch($baseMuServer . 'GameServer\\Data\\GameServerInfo - Event.dat');
        if (!empty($gsSwitch)) {
            foreach ($allEvents as &$eventItem) {
                $cleanName = strtolower(str_replace([' ', '_', '-'], '', $eventItem['name']));
                foreach ($gsSwitch as $switchKey => $switchEnabled) {
                    if (str_contains($cleanName, $switchKey) || str_contains($switchKey, $cleanName)) {
                        if (!$switchEnabled) {
                            $eventItem['enabled'] = false;
                        }
                    }
                }
            }
            unset($eventItem);
        }

        return $allEvents;
    }

    /**
     * Leitor do InvasionManager.dat (preservando nomes customizados da sessão 1)
     */
    private static function parseInvasionManager(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $defaultNames = [
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
            12 => "Invasão Custom 1",
            13 => "Narcondra",
            14 => "Grand Wizard",
            15 => "Cavalry Captain",
            16 => "Quartermaster",
            17 => "Combat Instructor",
            18 => "Knight Commander",
            19 => "Master Assassin",
            20 => "Kundun K7"
        ];

        $currentSection = null;
        $timestamps = [];
        $enabled = [];
        $names = $defaultNames;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '//')) {
                continue;
            }

            if (preg_match('/^(\d+)$/', $trimmed, $m)) {
                $currentSection = intval($m[1]);
                continue;
            }

            if ($trimmed === 'end') {
                $currentSection = null;
                continue;
            }

            $parts = preg_split('/\s+/', $trimmed);

            // Seção 0: Horários por Index
            if ($currentSection === 0 && count($parts) >= 8) {
                $idx = intval($parts[0]);
                $rawDow = trim($parts[4]);
                $dowName = ($rawDow === '*' || $rawDow === '-1') ? 'Todos os Dias' : (self::$daysOfWeek[$rawDow] ?? $rawDow);
                
                $h = $parts[5];
                $m = $parts[6];
                
                $timestamps[$idx] = [
                    'year' => $parts[1],
                    'month' => $parts[2],
                    'day' => $parts[3],
                    'dow' => $dowName,
                    'hour' => $h,
                    'minute' => $m,
                    'second' => $parts[7],
                ];
            }

            // Seção 1: Status Ativo e Nome
            if ($currentSection === 1 && count($parts) >= 2) {
                $idx = intval($parts[0]);
                $enabled[$idx] = true;

                // Extrai nome entre aspas ou junta tokens
                if (preg_match('/"([^"]+)"/', $trimmed, $matchQuote)) {
                    $names[$idx] = $matchQuote[1];
                }
            }
        }

        $events = [];
        foreach ($names as $idx => $name) {
            $ts = $timestamps[$idx] ?? ['dow' => 'Todos os Dias', 'hour' => '*', 'minute' => '*'];
            $dow = $ts['dow'] ?? 'Todos os Dias';
            $hour = $ts['hour'] ?? '*';
            $minute = $ts['minute'] ?? '*';

            $allHours = [];
            if ($minute !== '*') {
                if ($hour === '*') {
                    $allHours[] = '*:' . str_pad($minute, 2, '0', STR_PAD_LEFT);
                } else {
                    $allHours[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT);
                }
            }

            $events[] = [
                'name' => $name,
                'category' => 'Invasão',
                'timestamp' => $ts,
                'all_hours' => $allHours,
                'all_schedules' => [
                    [
                        'dow' => $dow,
                        'hour' => $hour,
                        'minute' => $minute,
                    ]
                ],
                'enabled' => !empty($enabled[$idx]),
                'dow' => $dow,
            ];
        }

        return $events;
    }

    /**
     * Lê os arquivos de eventos em Data\Event\*.dat (Blood Castle, Chaos Castle, Devil Square, Crywolf, etc.)
     */
    private static function parseStandardEventFolder(string $dirPath): array
    {
        if (!is_dir($dirPath)) {
            return [];
        }

        $events = [];
        $files = glob($dirPath . DIRECTORY_SEPARATOR . '*.dat') ?: [];

        foreach ($files as $file) {
            $baseName = basename($file);
            if (strcasecmp($baseName, 'InvasionManager.dat') === 0) {
                continue; // Já processado
            }

            $nameWithoutExt = pathinfo($baseName, PATHINFO_FILENAME);
            $parsed = self::parseGenericMuEventFile($file, $nameWithoutExt, 'Evento');
            if ($parsed) {
                $events[] = $parsed;
            }
        }

        return $events;
    }

    /**
     * Lê arquivos custom em Data\Custom\*.txt estritamente para os 4 eventos permitidos
     */
    private static function parseCustomFolder(string $dirPath): array
    {
        if (!is_dir($dirPath)) {
            return [];
        }

        $allowedCustomEvents = [
            'customarena' => 'Custom Arena',
            'customeventdrop' => 'Custom Event Drop',
            'customonlinelottery' => 'Custom Online Lottery',
            'customquiz' => 'Custom Quiz',
        ];

        $events = [];

        foreach ($allowedCustomEvents as $filePrefix => $displayName) {
            $matchedFiles = glob($dirPath . DIRECTORY_SEPARATOR . '*' . $filePrefix . '*.txt') ?: [];
            if (empty($matchedFiles)) {
                $matchedFiles = glob($dirPath . DIRECTORY_SEPARATOR . '*' . $filePrefix . '*.dat') ?: [];
            }

            foreach ($matchedFiles as $filePath) {
                $parsed = self::parseGenericMuEventFile($filePath, $displayName, 'Custom');
                if ($parsed) {
                    $events[] = $parsed;
                    break;
                }
            }
        }

        return $events;
    }

    /**
     * Parser genérico para arquivos do X-Team (.dat / .txt) com validação estrita de colunas e horários
     */
    private static function parseGenericMuEventFile(string $filePath, string $rawName, string $category): ?array
    {
        // Ignora gerenciadores de bônus, drops de itens gerais ou arquivos não-evento
        if (preg_match('/bonus|manager|item|drop(?!event)|box/i', $rawName) && !preg_match('/eventdrop/i', $rawName)) {
            return null;
        }

        $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return null;
        }

        // Limpa sufixos redundantes como 'Event' ou 'Evento' do nome exibido
        $cleanName = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $rawName));
        $cleanName = trim(preg_replace('/\b(Event|Evento)\b/i', '', $cleanName));
        $enabled = true;
        $currentSection = null;
        $headerColumns = [];
        $validSchedules = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            // Detecta cabeçalhos comentados como //Year Month Day DoW Hour Minute Second ou //Mode State Month Day DayOfWeek Hour Minute ContinuanceTime
            if (str_starts_with($trimmed, '//')) {
                $commentContent = strtolower(trim(substr($trimmed, 2)));
                if (str_contains($commentContent, 'hour') && str_contains($commentContent, 'minute')) {
                    $headerColumns = preg_split('/\s+/', $commentContent);
                }
                continue;
            }

            if (preg_match('/^(\d+)$/', $trimmed, $m)) {
                $currentSection = intval($m[1]);
                // Se for seção > 0 em eventos clássicos, não é seção de horários de abertura (são fases internas/monstros)
                if ($currentSection > 0) {
                    $headerColumns = [];
                }
                continue;
            }

            if ($trimmed === 'end') {
                $currentSection = null;
                $headerColumns = [];
                continue;
            }

            // Flags de ativação tipo: EventSwitch = 1 ou Enable = 1
            if (preg_match('/(switch|enable|active)\s*=\s*(\d)/i', $trimmed, $sw)) {
                $enabled = intval($sw[2]) === 1;
            }

            // Processa linhas com números e asteriscos
            $parts = preg_split('/\s+/', $trimmed);
            if (count($parts) < 3) {
                continue;
            }

            // Em arquivos de eventos do MuOnline, horários de início/abertura residem SEMPRE na Seção 0 (ou no arquivo todo se não houver seções numéricas)
            // Seções 1, 2, 3... são exclusivamente drops, monstros, NPCs ou transições de estado
            if ($currentSection !== null && $currentSection > 0) {
                continue;
            }

            $hourVal = null;
            $minVal = null;
            $dowVal = '*';
            $monthVal = '*';

            // Só processa horários se houver cabeçalho explícito contendo 'hour' e 'minute'
            // OU se for estritamente a Seção 0 (ou sem seção) com padrão clássico completo de 7 ou 8 colunas de tempo
            if (!empty($headerColumns)) {
                $hourIdx = array_search('hour', $headerColumns);
                $minIdx = array_search('minute', $headerColumns);
                $dowIdx = array_search('dayofweek', $headerColumns);
                if ($dowIdx === false) {
                    $dowIdx = array_search('dow', $headerColumns);
                }
                $monthIdx = array_search('month', $headerColumns);

                if ($hourIdx !== false && isset($parts[$hourIdx])) {
                    $hourVal = $parts[$hourIdx];
                }
                if ($minIdx !== false && isset($parts[$minIdx])) {
                    $minVal = $parts[$minIdx];
                }
                if ($dowIdx !== false && isset($parts[$dowIdx])) {
                    $rawDow = trim($parts[$dowIdx]);
                    // Se for 1 a 7 e o cabeçalho for dayofweek (Crywolf) ou valor 7
                    if (isset(self::$daysOfWeek1To7[$rawDow])) {
                        $dowVal = self::$daysOfWeek1To7[$rawDow];
                    } else {
                        $dowVal = self::$daysOfWeek[$rawDow] ?? $rawDow;
                    }
                }
                if ($monthIdx !== false && isset($parts[$monthIdx])) {
                    $monthVal = $parts[$monthIdx];
                }
            } elseif ($currentSection === 0 || $currentSection === null) {
                // Heurística de padrão X-Team sem cabeçalho específico SOMENTE se for seção 0:
                // Padrão A: 7 colunas (Year Month Day DoW Hour Minute Second) -> ex: CastleDeep
                if (count($parts) >= 7 && ($parts[0] === '*' || is_numeric($parts[0])) && ($parts[1] === '*' || is_numeric($parts[1])) && ($parts[4] !== '*' && is_numeric($parts[4]))) {
                    $monthVal = $parts[1];
                    $dowVal = self::$daysOfWeek[$parts[3]] ?? $parts[3];
                    $hourVal = $parts[4];
                    $minVal = $parts[5];
                }
                // Padrão B: 8 colunas (Index Year Month Day DoW Hour Minute Second)
                elseif (count($parts) >= 8 && is_numeric($parts[0]) && ($parts[1] === '*' || is_numeric($parts[1])) && ($parts[5] !== '*' && is_numeric($parts[5]))) {
                    $monthVal = $parts[2];
                    $dowVal = self::$daysOfWeek[$parts[4]] ?? $parts[4];
                    $hourVal = $parts[5];
                    $minVal = $parts[6];
                } else {
                    continue;
                }
            } else {
                // Seção desconhecida sem cabeçalho de hora/minuto - ignora para não gerar 00:00 fantasma
                continue;
            }

            // Validação rigorosa dos valores:
            // Horário DEVE ser '*' ou número entre 0 e 23
            // Minuto DEVE ser '*' ou número entre 0 e 59
            if ($hourVal === null || $minVal === null) {
                continue;
            }

            $isValidHour = ($hourVal === '*' || (is_numeric($hourVal) && intval($hourVal) >= 0 && intval($hourVal) <= 23));
            $isValidMin = ($minVal === '*' || (is_numeric($minVal) && intval($minVal) >= 0 && intval($minVal) <= 59));

            if (!$isValidHour || !$isValidMin) {
                continue;
            }

            // Se tiver mês específico numérico diferente de '*' (ex: mês 1, 4 ou 5 anual), ignora evento sazonal/mensal estático
            if ($monthVal !== '*' && is_numeric($monthVal)) {
                $currentMonth = intval(now()->format('m'));
                if (intval($monthVal) !== $currentMonth) {
                    continue; // Não é o mês ativo deste evento
                }
            }

            $validSchedules[] = [
                'dow' => $dowVal ?? '*',
                'hour' => $hourVal,
                'minute' => $minVal,
            ];
        }

        if (empty($validSchedules)) {
            return null;
        }

        // Extrai todos os dias da semana únicos configurados
        $dows = [];
        $hasWildcardDow = false;
        foreach ($validSchedules as $sched) {
            $d = $sched['dow'];
            if ($d === '*' || $d === '-1' || strtolower($d) === 'todos os dias') {
                $hasWildcardDow = true;
            } elseif (!empty($d)) {
                $dows[] = $d;
            }
        }
        $dows = array_unique($dows);

        // Se tiver dias específicos (ex: Crywolf em Quarta-feira e Sábado)
        $dowDisplay = '*';
        if (!empty($dows)) {
            $dowDisplay = implode(', ', $dows);
        } elseif ($hasWildcardDow) {
            $dowDisplay = 'Todos os Dias';
        }

        // Monta lista de todos os horários configurados para exibição
        $formattedHours = [];
        foreach ($validSchedules as $sched) {
            if ($sched['hour'] === '*') {
                $formattedHours[] = '*:' . str_pad($sched['minute'], 2, '0', STR_PAD_LEFT);
            } else {
                $formattedHours[] = str_pad($sched['hour'], 2, '0', STR_PAD_LEFT) . ':' . str_pad($sched['minute'], 2, '0', STR_PAD_LEFT);
            }
        }
        $formattedHours = array_values(array_unique($formattedHours));

        // Timestamp primário
        $primaryTimestamp = $validSchedules[0];

        return [
            'name' => $cleanName,
            'category' => $category,
            'timestamp' => $primaryTimestamp,
            'all_hours' => $formattedHours,
            'all_schedules' => $validSchedules,
            'enabled' => $enabled,
            'dow' => $dowDisplay,
        ];
    }

    /**
     * Mapeia chaves liga/desliga de GameServerInfo - Event.dat
     */
    private static function parseGameServerEventSwitch(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $switches = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '//')) {
                continue;
            }

            if (preg_match('/([a-zA-Z0-9_]+EventSwitch|[a-zA-Z0-9_]+EventEnable)\s*=\s*(\d)/i', $trimmed, $m)) {
                $key = strtolower(str_replace(['eventswitch', 'eventenable', '_'], '', $m[1]));
                $switches[$key] = intval($m[2]) === 1;
            }
        }

        return $switches;
    }
}
