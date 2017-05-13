<?php
namespace PpitLearning\ViewHelper;

use PpitCore\Model\Context;
use PpitLearning\Model\TestResult;

class SsmlTestResultViewHelper
{
	public static function formatXls($workbook, $view)
	{
		$context = Context::getCurrent();
		$translator = $context->getServiceManager()->get('translator');

		$title = (isset ($context->getConfig('testResult/search')['title']) ? $context->getConfig('testResult/search')['title'][$context->getLocale()] : $this->translate('Accounts', 'ppit-commitment', $context->getLocale()));
		
		// Set document properties
		$workbook->getProperties()->setCreator('P-PIT')
			->setLastModifiedBy('P-PIT')
			->setTitle($title)
			->setSubject($title)
			->setDescription($title)
			->setKeywords($title)
			->setCategory($title);

		$sheet = $workbook->getActiveSheet();
		
		$i = 0;
		$colNames = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L', 13 => 'M', 14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S', 20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z', 27 => 'AA', 28 => 'AB', 29 => 'AC', 30 => 'AD', 31 => 'AE', 32 => 'AF', 33 => 'AG', 34 => 'AH');
		
		foreach($context->getConfig('testResult/export') as $propertyId => $unused) {
			$property = $context->getConfig('testResult')['properties'][$propertyId];
			if ($property['type'] == 'repository') $property = $context->getConfig($property['definition']);
			$i++;
			$sheet->setCellValue($colNames[$i].'1', $property['labels'][$context->getLocale()]);
		}
		$sheet->setCellValue($colNames[$i++].'1', 'Question Id');
		$sheet->setCellValue($colNames[$i++].'1', 'Réponse');
		$sheet->setCellValue($colNames[$i++].'1', 'Score');
		$sheet->setCellValue($colNames[$i++].'1', 'Categories');
		
		$j = 1;
		foreach ($view->results as $result) {
			foreach ($result->answers as $questionId => $answer) {
				$j++;
				$i = 0;
				foreach($context->getConfig('testResult/export') as $propertyId => $unused) {
					$property = $context->getConfig('testResult')['properties'][$propertyId];
					if ($property['type'] == 'repository') $property = $context->getConfig($property['definition']);
					$i++;
					if ($property['type'] == 'date') $sheet->setCellValue($colNames[$i].$j, $context->decodeDate($result->properties[$propertyId]));
					elseif ($property['type'] == 'number') $sheet->setCellValue($colNames[$i].$j, $context->formatFloat($result->properties[$propertyId], 2));
					elseif ($property['type'] == 'select')  $sheet->setCellValue($colNames[$i].$j, (array_key_exists($result->properties[$propertyId], $property['modalities'])) ? $property['modalities'][$result->properties[$propertyId]][$context->getLocale()] : $result->properties[$propertyId]);
					else $sheet->setCellValue($colNames[$i].$j, $result->properties[$propertyId]);
				}
				$sheet->setCellValue($colNames[$i++].$j, $questionId);
				$sheet->setCellValue($colNames[$i++].$j, $answer);
				$sheet->setCellValue($colNames[$i++].$j, $result->content['parts'][$questionId]['modalities'][$answer]['value']);
				$sheet->setCellValue($colNames[$i++].$j, implode(',', $result->content['parts'][$questionId]['categories']));
			}
		}
		$i = 0;
		foreach($context->getConfig('testResult/export') as $propertyId => $property) {
			$i++;
			$sheet->getStyle($colNames[$i].'1')->getFont()->getColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingColor'], 1, 6));
			$sheet->getStyle($colNames[$i].'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingBackground'], 1, 6));
			$sheet->getStyle($colNames[$i].'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle($colNames[$i].'1')->getFont()->setBold(true);
			$sheet->getColumnDimension($colNames[$i])->setAutoSize(true);
		}
		$sheet->getStyle($colNames[$i].'1')->getFont()->getColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingColor'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingBackground'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle($colNames[$i].'1')->getFont()->setBold(true);
		$sheet->getColumnDimension($colNames[$i++])->setAutoSize(true);

		$sheet->getStyle($colNames[$i].'1')->getFont()->getColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingColor'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingBackground'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle($colNames[$i].'1')->getFont()->setBold(true);
		$sheet->getColumnDimension($colNames[$i++])->setAutoSize(true);

		$sheet->getStyle($colNames[$i].'1')->getFont()->getColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingColor'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingBackground'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle($colNames[$i].'1')->getFont()->setBold(true);
		$sheet->getColumnDimension($colNames[$i++])->setAutoSize(true);

		$sheet->getStyle($colNames[$i].'1')->getFont()->getColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingColor'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB(substr($context->getConfig('styleSheet')['panelHeadingBackground'], 1, 6));
		$sheet->getStyle($colNames[$i].'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle($colNames[$i].'1')->getFont()->setBold(true);
		$sheet->getColumnDimension($colNames[$i++])->setAutoSize(true);
	}
}