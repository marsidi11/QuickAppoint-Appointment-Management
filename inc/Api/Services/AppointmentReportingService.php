<?php

namespace Inc\Api\Services;

use Inc\Api\Repositories\AppointmentRepository;
use Inc\Api\Callbacks\TimeSlotGenerator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class AppointmentReportingService
{
    private $appointmentRepository;
    private $timeSlotGenerator;

    public function __construct(AppointmentRepository $appointmentRepository, TimeSlotGenerator $timeSlotGenerator)
    {
        $this->appointmentRepository = $appointmentRepository;
        $this->timeSlotGenerator = $timeSlotGenerator;
    }

    public function getAvailableTimeSlots(string $date, int $serviceDuration): array
    {
        $params = [
            'date' => $date,
            'serviceDuration' => $serviceDuration,
        ];
        return $this->timeSlotGenerator->generateOptimizedTimeSlots($params);
    }

    public function filterAppointments(?string $search, ?array $dateFilters, ?string $dateRange, ?array $statusFilters, int $page, int $per_page): array
    {
        $offset = ($page - 1) * $per_page;

        return $this->appointmentRepository->filterAppointments($search, $dateFilters, $dateRange, $statusFilters, $per_page, $offset);
    }

    public function generateReport($startDate, $endDate)
    {
        $appointments = $this->appointmentRepository->getAppointmentsForReport($startDate, $endDate);

        $spreadsheet = new Spreadsheet();
        
        // Create detailed appointments sheet
        $this->createDetailedAppointmentsSheet($spreadsheet, $appointments);
        
        // Create service popularity sheet
        $this->createServicePopularitySheet($spreadsheet, $appointments);
        
        // Create time slot popularity sheet
        $this->createTimeSlotPopularitySheet($spreadsheet, $appointments);
        
        // Create revenue analysis sheet
        $this->createRevenueAnalysisSheet($spreadsheet, $appointments);
        
        // Create customer insights sheet
        $this->createCustomerInsightsSheet($spreadsheet, $appointments);

        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'appointments_report_' . date('Y-m-d') . '.xlsx';
        $filepath = wp_upload_dir()['path'] . '/' . $filename;
        $writer->save($filepath);

        return $filepath;
    }

    private function createDetailedAppointmentsSheet($spreadsheet, $appointments)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detailed Appointments');

        // Add headers
        $headers = ['Date', 'Start Time', 'End Time', 'Name', 'Email', 'Phone', 'Services', 'Total Price', 'Status'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Add data
        $row = 2;
        foreach ($appointments as $appointment) {
            $sheet->fromArray([
                $appointment->date,
                $appointment->startTime,
                $appointment->endTime,
                $appointment->name . ' ' . $appointment->surname,
                $appointment->email,
                $appointment->phone,
                $appointment->service_names,
                $appointment->total_price,
                $appointment->status
            ], NULL, 'A' . $row);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function createServicePopularitySheet($spreadsheet, $appointments)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Service Popularity');

        $serviceCount = [];
        foreach ($appointments as $appointment) {
            $services = explode(', ', $appointment->service_names);
            foreach ($services as $service) {
                if (!isset($serviceCount[$service])) {
                    $serviceCount[$service] = 0;
                }
                $serviceCount[$service]++;
            }
        }

        arsort($serviceCount);

        $sheet->setCellValue('A1', 'Service');
        $sheet->setCellValue('B1', 'Bookings');

        $row = 2;
        foreach ($serviceCount as $service => $count) {
            $sheet->setCellValue('A' . $row, $service);
            $sheet->setCellValue('B' . $row, $count);
            $row++;
        }

        // Create a pie chart
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Service Popularity!$A$2:$A$' . ($row - 1), NULL, count($serviceCount)),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Service Popularity!$B$2:$B$' . ($row - 1), NULL, count($serviceCount)),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_PIECHART,
            NULL,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $dataSeriesValues
        );

        $plot = new PlotArea(NULL, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, NULL, false);
        $title = new Title('Service Popularity');

        $chart = new Chart(
            'chart1',
            $title,
            $legend,
            $plot
        );

        $chart->setTopLeftPosition('D2');
        $chart->setBottomRightPosition('K15');

        $sheet->addChart($chart);
    }

    private function createTimeSlotPopularitySheet($spreadsheet, $appointments)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Time Slot Popularity');

        $timeSlots = [];
        foreach ($appointments as $appointment) {
            $hour = date('H', strtotime($appointment->startTime));
            if (!isset($timeSlots[$hour])) {
                $timeSlots[$hour] = 0;
            }
            $timeSlots[$hour]++;
        }

        ksort($timeSlots);

        $sheet->setCellValue('A1', 'Hour');
        $sheet->setCellValue('B1', 'Bookings');

        $row = 2;
        foreach ($timeSlots as $hour => $count) {
            $sheet->setCellValue('A' . $row, $hour . ':00');
            $sheet->setCellValue('B' . $row, $count);
            $row++;
        }

        // Create a column chart
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Time Slot Popularity!$A$2:$A$' . ($row - 1), NULL, count($timeSlots)),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Time Slot Popularity!$B$2:$B$' . ($row - 1), NULL, count($timeSlots)),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            NULL,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $dataSeriesValues
        );

        $plot = new PlotArea(NULL, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, NULL, false);
        $title = new Title('Time Slot Popularity');

        $chart = new Chart(
            'chart2',
            $title,
            $legend,
            $plot
        );

        $chart->setTopLeftPosition('D2');
        $chart->setBottomRightPosition('K15');

        $sheet->addChart($chart);
    }

    private function createRevenueAnalysisSheet($spreadsheet, $appointments)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Revenue Analysis');

        $revenueByDate = [];
        foreach ($appointments as $appointment) {
            $date = $appointment->date;
            if (!isset($revenueByDate[$date])) {
                $revenueByDate[$date] = 0;
            }
            $revenueByDate[$date] += $appointment->total_price;
        }

        ksort($revenueByDate);

        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Revenue');

        $row = 2;
        foreach ($revenueByDate as $date => $revenue) {
            $sheet->setCellValue('A' . $row, $date);
            $sheet->setCellValue('B' . $row, $revenue);
            $row++;
        }

        // Create a line chart
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Revenue Analysis!$A$2:$A$' . ($row - 1), NULL, count($revenueByDate)),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Revenue Analysis!$B$2:$B$' . ($row - 1), NULL, count($revenueByDate)),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            NULL,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $dataSeriesValues
        );

        $plot = new PlotArea(NULL, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, NULL, false);
        $title = new Title('Daily Revenue');

        $chart = new Chart(
            'chart3',
            $title,
            $legend,
            $plot
        );

        $chart->setTopLeftPosition('D2');
        $chart->setBottomRightPosition('K15');

        $sheet->addChart($chart);
    }

    private function createCustomerInsightsSheet($spreadsheet, $appointments)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Customer Insights');

        $customerBookings = [];
        $customerSpending = [];

        foreach ($appointments as $appointment) {
            $email = $appointment->email;
            if (!isset($customerBookings[$email])) {
                $customerBookings[$email] = 0;
                $customerSpending[$email] = 0;
            }
            $customerBookings[$email]++;
            $customerSpending[$email] += $appointment->total_price;
        }

        arsort($customerBookings);
        arsort($customerSpending);

        $sheet->setCellValue('A1', 'Top Customers by Bookings');
        $sheet->setCellValue('A2', 'Email');
        $sheet->setCellValue('B2', 'Bookings');

        $row = 3;
        $count = 0;
        foreach ($customerBookings as $email => $bookings) {
            if ($count >= 10) break;
            $sheet->setCellValue('A' . $row, $email);
            $sheet->setCellValue('B' . $row, $bookings);
            $row++;
            $count++;
        }

        $sheet->setCellValue('D1', 'Top Customers by Spending');
        $sheet->setCellValue('D2', 'Email');
        $sheet->setCellValue('E2', 'Total Spent');

        $row = 3;
        $count = 0;
        foreach ($customerSpending as $email => $spent) {
            if ($count >= 10) break;
            $sheet->setCellValue('D' . $row, $email);
            $sheet->setCellValue('E' . $row, $spent);
            $row++;
            $count++;
        }

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}