<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\XLSX\Options;

class ImporController extends Controller
{   
    /**
     * Impor and Store Excel data
     *
     * @param Request $request
     * @return boolean
     */
    public function store(Request $request): JsonResponse {
        $excel = $request->file('excel');
        $name = 'menus.xlsx';
        $excel->move('./', $name);

        $response = $this->retrieve($name);

        return response()->json([
            'status' => true,
            'data' => $response
        ]);
    }

    /**
     * Load data Excel
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function load(Request $request): JsonResponse {
        $response = $this->retrieve('menus.xlsx', $request->perpage);
        return response()->json([
            'status' => true,
            'data' => $response
        ]);
    }

    /**
     * Retrieve data from Excel file
     *
     * @param string $filename
     * @param integer $perpage
     * @return Array
     */
    protected function retrieve(string $filename, $perpage = 25): Array {
        $options = new Options();
        $options->SHOULD_FORMAT_DATES = true;

        $reader = new Reader($options);
        $reader->open($filename);

        $data = [];
        
        $format = function($date) {
            return Carbon::parse($date)->format('d F Y H:i');
        };

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getIndex() === 0) {
                foreach ($sheet->getRowIterator() as $key => $row) {
                    if($key < 2) {
                        continue;
                    }
                    
                    $cells = $row->getCells();

                    $data[] = [
                        'name'      => $cells[0]->getValue(),
                        'ch_name'   => $cells[1]->getValue(),
                        'price'     => $cells[2]->getValue(),
                        'discount'  => $cells[3]->getValue(),
                        'isSpecial' => $cells[4]->getValue(),
                        'priority'  => $cells[5]->getValue(),
                        'description' => $cells[6]->getValue(),
                        'isShow'     => $cells[7]->getValue(),
                        'created_at' => $format($cells[8]->getValue()), 
                        'created_by' => $cells[9]->getValue(),
                        'updated_at' => $format($cells[10]->getValue()),
                        'updated_by' => $cells[11]->getValue()
                    ];
                }
            }
        }
        
        $reader->close();

        $collection = collect($data)->chunk($perpage);

        return $collection->toArray();
    }
}
