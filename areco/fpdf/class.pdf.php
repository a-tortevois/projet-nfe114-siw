<?php
class PDF extends FPDF
{
	protected $Y = INIT_Y;

	function Header()
	{
		global $label;
		
		// $this->Rect(10,10,190,18);
		// $this->Rect(10,10,45,18);		
		$this->SetFont('Arial','B',16);
		$this->Image('logo.png',10,10,45);
		$s = $label['title'];
		$l = ceil((145-$this->GetStringWidth($s))/2)+55;
		$this->Text($l,21,$s);
		$this->SetDrawColor(53, 177, 213);
		$this->Line(10, 28, 200, 28);
		
		$this->SetDrawColor(0);

		$this->SetFont('Arial','',11);
		$this->SetTextColor(0);
	}
	
	function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->SetTextColor(128);
		$this->Cell(0,10,'Page '.$this->PageNo().' / {nb}',0,0,'C');
	}
	
	function AddLine($label, $value = '', $color = '')
	{
		$this->SetXY(10,$this->Y);
		
		// Label
		$s = $label;
		$l = ceil($this->GetStringWidth($s));
		$this->Cell($l, LH, $s, BORDERDEBUG);
		
		// Value
		if( !empty($value) )
		{
			$this->SetFont('Arial','B',11);
			switch( $color )
			{
				case 'green' :
					$this->SetTextColor(0, 176, 80);
				break;
				
				case 'red' : 
					$this->SetTextColor(255, 0, 0);
				break;
				
				default :
					$this->SetTextColor(0);
			}	
			
			$s = $value;
			$l = ceil($this->GetStringWidth($s));
			$this->Cell($l, LH, $s, BORDERDEBUG);
			
			$this->SetFont('Arial','',11);
			$this->SetTextColor(0);
		}
		
		$this->Y += LH;
	}
	
	function CellResult( $result, $color = '' )
	{
		if( empty( $result ) )
		{
			$result = 0;
		}

		$this->SetFont('Arial','B',11);
		switch( $color )
		{
			case 'green' :
				$this->SetTextColor(0, 176, 80);
			break;
			
			case 'red' : 
				$this->SetTextColor(255, 0, 0);
			break;
			
			default :
				$this->SetTextColor(0);
		}
		
		$this->Cell(30, LH, $result, 1, 0, 'C');
		
		$this->SetFont('Arial','',11);
		$this->SetTextColor(0);
	}

	function TestResult( $result )
	{
		global $label;

		$y = $this->Get_Y();
		$this->SetXY(90,$y-2.5*LH);
		
		$this->SetFont('Arial','',11);
		$this->SetTextColor(0);		
		$this->Cell(90, LH, $label['result'], 1, 0, 'C');
		
		$this->SetXY(90,$y-1.5*LH);
		$this->SetFont('Arial','B',16);
		
		if( $result == 1 ) // $result = 1 -> echec
		{
			$this->SetTextColor(255, 0, 0);
			$this->Cell(90, 2*LH, $label['result_nok'], 1, 1, 'C');
		}
		else // $result = 0 -> pass
		{
			$this->SetTextColor(0, 176, 80);
			$this->Cell(90, 2*LH, $label['result_ok'], 1, 1, 'C');
		}
		
		$this->SetFont('Arial','',11);
		$this->SetTextColor(0);
	}
	
	function Get_Y()
	{
		return $this->Y;
	}

	function Set_Y($val)
	{
		$this->Y = $val;
	}
}
?>