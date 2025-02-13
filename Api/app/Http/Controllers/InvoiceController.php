<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InvoiceController extends Controller
{
    public function getInvoicesList(Request $request)
    {
        try {
            $invoice_list = Invoice::getInvoiceList($request->all());
            return $this->successReturn($invoice_list, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function saveInvoice(Request $request)
    {
        $request_data = json_decode($request['form'], true);
        $validator = ValidationService::saveInvoiceValidator($request_data);

        if ($validator->passes()) {
            $data = Validator::make($request->all(), [
                'invoice_file' => 'sometimes|required|mimes:xls,xlsx'
            ]);
            if ($data->fails()) {
                return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
        }

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        } else {
            try {
                $formData = json_decode($request['form'], true);

                if ($request->hasFile('invoice_file')) {
                    $file = $request->file('invoice_file');
                    $filePath = $file->getRealPath();

                    // Debug statement
                    Log::info("File Path: " . $filePath);

                    try {
                        $spreadsheet = IOFactory::load($filePath);
                        $sheet = $spreadsheet->getActiveSheet();
                        $highestRow = $sheet->getHighestRow();
                        $specificData = [];

                        $loading_date = $this->excelDateToStandardDate($sheet->getCellByColumnAndRow(2, 1)->getValue());
                        $loading_time = $sheet->getCellByColumnAndRow(2, 2)->getValue();
                        $loading_no = $sheet->getCellByColumnAndRow(2, 3)->getValue();
                        // Start reading from the 8th row
                        for ($row = 8; $row <= $highestRow; $row++) {
                            $invoiceNo = $sheet->getCellByColumnAndRow(1, $row)->getValue();  // Column A
//                            $date = $sheet->getCellByColumnAndRow(2, $row)->getValue();       // Column B
                            $date = $this->excelDateToStandardDate($sheet->getCellByColumnAndRow(2, $row)->getValue()); // Column B
                            $name = $sheet->getCellByColumnAndRow(3, $row)->getValue();       // Column C
                            $amount = $sheet->getCellByColumnAndRow(4, $row)->getValue();     // Column D
                            $remarks = $sheet->getCellByColumnAndRow(5, $row)->getValue();    // Column E

                            $shopId = null;
                            if ($name != null) {
                                $shop = DB::table('shops')->where('shop_name', $name)->first();
                                if ($shop) {
                                    $shopId = $shop->id;
                                }
                            }
                            if (empty($invoiceNo) && empty($date) && empty($name) && empty($amount) && empty($remarks)) {
                                // Skip empty rows
                                continue;
                            }

                            $specificData[] = [
                                'invoice_no' => $invoiceNo,
                                'date' => $date,
                                'shop_id' => $shopId,
                                'amount' => $amount,
                                'remarks' => $remarks
                            ];
                        }

                        // Insert data into the database
                        foreach ($specificData as $row) {
                            DB::table('invoices')->insert([
                                'invoice_no' => $row['invoice_no'],
                                'date' => $row['date'],
                                'shop_id' => $row['shop_id'],
                                'amount' => $row['amount'],
                                'remark' => $row['remarks'],
                                'route_id' => $formData['route_id']['id'],
                                'is_active' => $formData['is_active'],
                                'loading_date' => $loading_date,
                                'loading_time' => $loading_time,
                                'loading_no' => $loading_no,
                                'agency_id' => auth()->user()->agency_id,
                                'created_at' => now(),
                                'created_user_id' => auth()->user()->id,
                            ]);
                        }

                    } catch (\Exception $e) {
                        Log::error("Error loading spreadsheet: " . $e->getMessage());
                        return $this->errorReturn([], 'Error processing the Excel file.', ResponseAlias::HTTP_BAD_REQUEST);
                    }
                }
                return $this->successReturn([], 'New Invoice added successfully', ResponseAlias::HTTP_CREATED);
            } catch (\Exception $e) {
                Log::error($e);
                return $this->errorReturn([], 'Invoice creation failed.', ResponseAlias::HTTP_BAD_REQUEST);
            }
        }
    }

    private function excelDateToStandardDate($excelDate)
    {
        if (is_numeric($excelDate)) {
            $unixDate = ($excelDate - 25569) * 86400;
            $standardDate = gmdate("Y-m-d", $unixDate);
            return $standardDate;
        }
        return $excelDate; // Return as-is if it's not a numeric Excel date
    }

    public function updateInvoice(Request $request)
    {
        $validator = ValidationService::updateInvoiceValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Invoice::updateInvoice($request->all());
            return $this->successReturn([], 'Invoice updated successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Invoice update failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteInvoice(Request $request)
    {
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Invoice::deleteInvoice($request->all());
            return $this->successReturn([], 'Data Deleted Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
