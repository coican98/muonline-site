<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Services\PlayerDisconnectService;

class AdminController extends Controller
{
    public function index(){
        if(!(Auth::check() && Auth::user()->global_admin == 1)){
            return redirect('/')->with('error', 'Esta página está restrita a administradores!');
        }
        $csvData = session('csvData',null);
        $columns = ['Id', 'GameID1', 'GameID2', 'GameID3', 'GameID4', 'GameID5'];

        $settingsPath = storage_path('app/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $shopSettings = $settings['shop_settings'] ?? [];
        $shopPackages = $settings['shop_packages'] ?? [];
        $registrationBonus = $settings['registration_bonus'] ?? [
            'enabled' => true,
            'vip_type' => 1,
            'vip_days' => 3,
            'coin1' => 0,
            'coin2' => 0,
            'coin3' => 0,
        ];

        $newsList = \App\Services\NewsService::getAllForAdmin();

        return view('admin', [
            'results' => $csvData,
            'columns' => $columns,
            'shopSettings' => $shopSettings,
            'shopPackages' => $shopPackages,
            'registrationBonus' => $registrationBonus,
            'newsList' => $newsList,
        ]);
    }

    public function upload(Request $request){
        if ($request->has('is_external')) {
            $request->validate([
                'ext_name' => 'required|string',
                'ext_url' => 'required|url',
                'ext_size' => 'nullable|string',
            ]);
            DB::table('external_downloads')->insert([
                'name' => $request->ext_name,
                'link' => $request->ext_url,
                'size' => $request->ext_size ?? 'Desconhecido',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return redirect()->back()->with('success', 'Link externo salvo com sucesso!');
        }

        $request->validate([
            'file' => 'required|file',
        ]);        
        $file = $request->file("file");
        $extension = $file->getClientOriginalExtension();
        if($file->isValid()){
            if(in_array(strtolower($extension), ['exe', 'rar', 'zip'])){
                $stored = $file->storeAs('public/download',$file->getClientOriginalName());
                return redirect()->back()->with("success","Arquivo local salvo!");
            }else{
                $csvData = $this->readCsv($file);
                $sqlData = DB::table('AccountCharacter')->get()->toArray();
                $columns = ['Id', 'GameID1', 'GameID2', 'GameID3', 'GameID4', 'GameID5'];

                return view('admin', [
                    'csvData' => $csvData,
                    'sqlData' => $sqlData,
                    'columns' => $columns,
                ]);
            }
        }else{
            return redirect()->back()->with('error', 'O arquivo é inválido.');
        }
    }

    public function disconnectPlayer(Request $request, PlayerDisconnectService $disconnectService)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Esta página está restrita a administradores!');
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_]+$/'],
        ]);

        try {
            $disconnectService->disconnect($validated['username']);
            return redirect()->back()->with('success', "A conta {$validated['username']} foi desconectada.");
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }
    public function removeDownloadFile($download){
        $file = storage_path('app/public/download/'. $download);
        if(file_exists($file)){
            unlink($file);
            return redirect()->back()->with('success',"Arquivo $download removido com sucesso!");
        }else {
            return redirect()->back()->with('error', "Arquivo $file não existe.");
        }
    }

    public function readCsv($csvFile){
        $csv = fopen($csvFile,"r");

        $firstLine = fgets($csv);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($csv);
        $header = fgetcsv($csv, 0, $delimiter);

        if ($header == ['Id', 'GameID1', 'GameID2', 'GameID3', 'GameID4', 'GameID5']) {
            while (($row = fgetcsv($csv)) !== false) {
                // Ensure that the number of columns in the row matches the header
                if (count($row) == count($header)) {
                    $data[] = array_combine($header, $row);  // Combine header and data into associative array
                } else {
                    // Fill missing columns with null to ensure it matches the header length
                    $row = array_pad($row, count($header), null);
                    $data[] = array_combine($header, $row);
                }
            }
        }
    
        fclose($csv);
    
        return $data;
    }

    public function exportCsv(Request $request){
        $filename = 'accountCharacter.csv';
        
    }

    public function updateSettings(Request $request) {
        if(!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Sem acesso.');
        }

        $data = [
            'discord_url' => $request->input('discord_url'),
            'whatsapp_url' => $request->input('whatsapp_url'),
            'eula_text' => $request->input('eula_text'),
        ];
        
        // Mantem outras configurações
        $settingsPath = storage_path('app/settings.json');
        if (file_exists($settingsPath)) {
            $existing = json_decode(file_get_contents($settingsPath), true);
            foreach ($existing as $k => $v) {
                if (!isset($data[$k])) {
                    $data[$k] = $v;
                }
            }
        }
        
        file_put_contents($settingsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        
        return redirect()->back()->with('success', 'Configurações globais atualizadas com sucesso!');
    }

    public function updateShopSettings(Request $request)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Sem acesso.');
        }

        $settingsPath = storage_path('app/settings.json');
        $data = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];

        $data['shop_settings'] = [
            'coins' => [
                'coin1_name' => $request->input('coin1_name', 'WCoinC (WC)'),
                'coin2_name' => $request->input('coin2_name', 'WCoinP (WP)'),
                'coin3_name' => $request->input('coin3_name', 'Goblin Point (GP)'),
            ],
            'vip_tiers' => [
                '0' => $request->input('vip_0_name', 'Free'),
                '1' => $request->input('vip_1_name', 'Bronze'),
                '2' => $request->input('vip_2_name', 'Prata'),
                '3' => $request->input('vip_3_name', 'Ouro'),
            ],
            'mp_public_key' => $request->input('mp_public_key', ''),
            'mp_access_token' => $request->input('mp_access_token', ''),
            'mp_webhook_secret' => $request->input('mp_webhook_secret', ''),
        ];

        file_put_contents($settingsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return redirect()->back()->with('success', 'Configurações da Loja e Mercado Pago salvas com sucesso!');
    }

    public function updateRegistrationBonus(Request $request)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Sem acesso.');
        }

        $settingsPath = storage_path('app/settings.json');
        $data = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];

        $data['registration_bonus'] = [
            'enabled' => $request->has('bonus_enabled'),
            'vip_type' => (int)$request->input('bonus_vip_type', 0),
            'vip_days' => (int)$request->input('bonus_vip_days', 0),
            'coin1' => (int)$request->input('bonus_coin1', 0),
            'coin2' => (int)$request->input('bonus_coin2', 0),
            'coin3' => (int)$request->input('bonus_coin3', 0),
        ];

        file_put_contents($settingsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return redirect()->back()->with('success', 'Configurações de bônus no cadastro salvas com sucesso!');
    }

    public function storePackage(Request $request)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Sem acesso.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.5',
            'vip_type' => 'required|integer|between:0,3',
            'vip_days' => 'nullable|integer|min:0',
            'coin1' => 'nullable|integer|min:0',
            'coin2' => 'nullable|integer|min:0',
            'coin3' => 'nullable|integer|min:0',
        ]);

        $settingsPath = storage_path('app/settings.json');
        $data = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $packages = $data['shop_packages'] ?? [];

        $newId = count($packages) > 0 ? max(array_column($packages, 'id')) + 1 : 1;

        $newPackage = [
            'id' => $newId,
            'name' => $request->input('name'),
            'description' => $request->input('description', ''),
            'price' => number_format((float)$request->input('price'), 2, '.', ''),
            'vip_type' => (int)$request->input('vip_type', 0),
            'vip_days' => (int)$request->input('vip_days', 0),
            'coin1' => (int)$request->input('coin1', 0),
            'coin2' => (int)$request->input('coin2', 0),
            'coin3' => (int)$request->input('coin3', 0),
            'highlight' => $request->has('highlight'),
            'active' => $request->has('active') ? (bool)$request->input('active') : true,
        ];

        $packages[] = $newPackage;
        $data['shop_packages'] = $packages;

        file_put_contents($settingsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return redirect()->back()->with('success', 'Novo pacote adicionado à Loja com sucesso!');
    }

    public function deletePackage($id)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Sem acesso.');
        }

        $settingsPath = storage_path('app/settings.json');
        $data = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $packages = $data['shop_packages'] ?? [];

        $filtered = array_values(array_filter($packages, function ($pkg) use ($id) {
            return $pkg['id'] != $id;
        }));

        $data['shop_packages'] = $filtered;
        file_put_contents($settingsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return redirect()->back()->with('success', 'Pacote removido da Loja com sucesso!');
    }

    public function togglePackage($id)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Sem acesso.');
        }

        $settingsPath = storage_path('app/settings.json');
        $data = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $packages = $data['shop_packages'] ?? [];

        $found = false;
        $newState = true;
        foreach ($packages as &$pkg) {
            if ($pkg['id'] == $id) {
                $currentState = !isset($pkg['active']) || (bool)$pkg['active'] === true;
                $pkg['active'] = !$currentState;
                $newState = $pkg['active'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            return redirect()->back()->with('error', 'Pacote não encontrado.');
        }

        $data['shop_packages'] = $packages;
        file_put_contents($settingsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $msg = $newState ? 'Pacote ativado com sucesso!' : 'Pacote desativado com sucesso!';
        return redirect()->back()->with('success', $msg);
    }

    public function updatePackage(Request $request, $id)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Sem acesso.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.5',
            'vip_type' => 'required|integer|between:0,3',
            'vip_days' => 'nullable|integer|min:0',
            'coin1' => 'nullable|integer|min:0',
            'coin2' => 'nullable|integer|min:0',
            'coin3' => 'nullable|integer|min:0',
        ]);

        $settingsPath = storage_path('app/settings.json');
        $data = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $packages = $data['shop_packages'] ?? [];

        $found = false;
        foreach ($packages as &$pkg) {
            if ($pkg['id'] == $id) {
                $pkg['name'] = $request->input('name');
                $pkg['description'] = $request->input('description', '');
                $pkg['price'] = number_format((float)$request->input('price'), 2, '.', '');
                $pkg['vip_type'] = (int)$request->input('vip_type', 0);
                $pkg['vip_days'] = (int)$request->input('vip_days', 0);
                $pkg['coin1'] = (int)$request->input('coin1', 0);
                $pkg['coin2'] = (int)$request->input('coin2', 0);
                $pkg['coin3'] = (int)$request->input('coin3', 0);
                $pkg['highlight'] = $request->has('highlight');
                if ($request->has('active_status_present')) {
                    $pkg['active'] = $request->has('active');
                }
                $found = true;
                break;
            }
        }

        if (!$found) {
            return redirect()->back()->with('error', 'Pacote não encontrado.');
        }

        $data['shop_packages'] = $packages;
        file_put_contents($settingsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return redirect()->back()->with('success', 'Pacote atualizado com sucesso!');
    }
}
