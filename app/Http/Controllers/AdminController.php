<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;

class AdminController extends Controller
{
    public function index(){
        if(!(Auth::check() && Auth::user()->global_admin == 1)){
            return redirect('/')->with('error', 'Esta página está restrita a administradores!');
        }
        $csvData = session('csvData',null);
        $columns = ['Id', 'GameID1', 'GameID2', 'GameID3', 'GameID4', 'GameID5'];
        return view('admin', [
            'results' => $csvData,
            'columns' => $columns,  // Pass the columns array to the view
        ]);
    }

    public function upload(Request $request){
        $request->validate([
            'file' => 'required|file',
        ]);        
        // dd($request);
        $file = $request->file("file");
        // dd($file->isValid());
        $extension = $file->getClientOriginalExtension();
        // dd($extension);
        if($file->isValid()){
            if($extension=="exe" || $extension== "rar"){
                $stored = $file->storeAs('public/download',$file->getClientOriginalName());
                return redirect()->back()->with("success","Arquivo salvo!");
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
}
