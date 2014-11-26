<?php

class FM_Month_Year extends Fieldmanager_Select {
	
	public function __construct( $label, $options = array() ) {
		
		parent::__construct( $label, $options );
	}
	
	public function form_element( $value ) {
	
		$months = array(
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);
		
		$years = range(date('Y', strtotime('+1 year')), date('Y', strtotime('-50 years')));
	
		$output = '';
		
		if( empty( $value ) ) {
			$value = date('Y-m-d H:i:s');
		}
		
		$month_opts = '';
		foreach( $months as $month_value => $month_name ) {
			
			$data = array(
				'name' => $month_name,
				'value' => $month_value
			);
			
			$month_opts .= $this->form_data_element( $data, array( date('m', strtotime( $value ) ) ) );
		}
	
		$year_opts = '';
		foreach( $years as $year ) {
		
			$data = array(
				'name' => $year,
				'value' => $year
			);
		
			$year_opts .= $this->form_data_element( $data, array( date('Y', strtotime($value) ) ) );
		}
	
		$output .= sprintf(
			'<select name="%s">%s</select>',
			$this->get_form_name( '[month]' ),
			$month_opts
		);
	
		$output .= sprintf(
			'<select name="%s">%s</select>',
			$this->get_form_name( '[year]' ),
			$year_opts
		);
		
		return $output;
	}

	/**
	 * Convert date to timestamp
	 * @param $value
	 * @param $current_value
	 * @return int unix timestamp
	 */
	public function presave( $value, $current_value = array() ) {
		
		if( empty( $value['year'] ) || empty( $value['month'] ) )  {
			return 0;
		}
		
		$date = $value['year'] . '-' . $value['month'] . '-01';
		
		return $date . ' 00:00:00';
	}
}