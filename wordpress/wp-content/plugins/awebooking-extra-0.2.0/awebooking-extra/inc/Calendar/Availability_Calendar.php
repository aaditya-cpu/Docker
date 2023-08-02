<?php
namespace AweBooking\Extra\Calendar;

use AweBooking\Factory;
use AweBooking\AweBooking;
use AweBooking\Hotel\Room_Type;
use AweBooking\Booking\Calendar;
use AweBooking\Support\Carbonate;
use AweBooking\Support\Abstract_Calendar;

class Availability_Calendar extends Abstract_Calendar {
	/**
	 * The room-type instance.
	 *
	 * @var Room_Type
	 */
	protected $room_type;

	/**
	 * Store day states.
	 *
	 * @var array
	 */
	protected $_day_states = [];

	/**
	 * Store day classes.
	 *
	 * @var array
	 */
	protected $_day_classes = [];

	protected $loop_month = 0;

	/**
	 * Create the Calendar.
	 *
	 * @param Room_Type $room_type Room type to display the Calendar.
	 * @param Carbonate $date      The date time will print the Calendar, if null given set as today.
	 * @param array     $options   Calendar options, @see the Abstract_Calendar::$defaults property.
	 */
	public function __construct( Room_Type $room_type, Carbonate $date = null, array $options = [] ) {
		$this->date = is_null( $date ) ? Carbonate::today() : $date;
		$this->room_type = $room_type;

		$this->defaults = array_merge( $this->defaults, [
			'layout'           => 'monthly', // 'monthly', 'yearly'.
			'changeover_day'   => false,

			// For monthly layout only.
			'number_of_months' => 2,
		]);

		parent::__construct( $options );
	}

	/**
	 * Display the Calendar.
	 *
	 * @return void
	 */
	public function display() {
		if ( 'yearly' === $this->get_option( 'layout' ) ) {
			echo $this->generate_year_calendar( $this->date->year );
			return;
		}

		$month = $this->date;
		$options = $this->get_options();

		// TODO: Move base_class as property.
		unset( $options['base_class'] );
		$options['changeover_day']   = $options['changeover_day'] ? 1 : 0;
		$options['hide_prev_months'] = $options['hide_prev_months'] ? 1 : 0;

		printf( '<div class="awebookingcal-ajax" data-layout="%1$s" data-date="%2$s" data-room-type="%3$d" data-options="%4$s">',
			esc_attr( $this->get_option( 'layout' ) ),
			esc_attr( $month->toDateString() ),
			esc_attr( $this->room_type->get_id() ),
			esc_attr( json_encode( $options ) )
		);

		$number_of_months = (int) $this->get_option( 'number_of_months' );
		if ( $number_of_months <= 1 ) {
			echo $this->generate_month_calendar( $this->date );
			return;
		} elseif ( $number_of_months > 1 ) {
			$number_of_months = ( $number_of_months > 6 ) ? 6 : $number_of_months;

			for ( $i = 0; $i < $number_of_months; $i++ ) {
				$this->loop_month = $i;
				echo $this->generate_month_calendar( $this->date->copy()->addMonths( $i ) );
			}
		}

		echo '</div>';
	}

	/**
	 * Generate HTML Calendar in a month.
	 *
	 * @param  Carbonate $month Month to generate.
	 * @return string
	 */
	protected function generate_month_calendar( Carbonate $month ) {
		$number_of_months = (int) $this->get_option( 'number_of_months' );

		echo '<div class="awebookingcal" data-date="' . $month->toDateString() . '"><div class="ui-datepicker-title">';

		if ( ( $number_of_months >= 1 && ! $this->is_prev_month( $month ) && $this->loop_month === 0 ) ) {
			echo '<span class="calendar__prev-month"></span>';
		}

		echo '<span class="' . $this->get_html_class( '&__title' ) . '">
				<span class="month">' . $this->get_month_name( $month->month, 'full' ) . '</span>&nbsp;<span class="year">' . $month->year . '</span>
			</span>';

		if ( $number_of_months >= 1 && ($this->loop_month + 1) === $number_of_months ) {
			echo '<span class="calendar__next-month"></span>';
		}

		echo '</div>';
		echo parent::generate_month_calendar( $month );
		echo '</div>';
	}

	protected function is_prev_month( Carbonate $month ) {
		return ( $this->today->year === $month->year && $month->month <= $this->today->month );
	}

	/**
	 * Prepare setup the data.
	 *
	 * @param  mixed  $input   Mixed input data.
	 * @param  string $context Context from Calendar.
	 * @return mixed
	 */
	protected function prepare_data( $input, $context ) {
		// Setup the BAT Calendar.
		$room_units = $this->room_type->get_rooms();
		if ( empty( $room_units ) ) {
			return [];
		}

		$calendar = Factory::create_availability_calendar( $room_units );

		if ( 'year' === $context && is_int( $input ) ) {
			$start_date = Carbonate::createFromDate( $input )->startOfYear();
			$end_date   = Carbonate::createFromDate( $input )->endOfYear();
		} elseif ( 'month' === $context && $input instanceof Carbonate ) {
			$start_date = $input->copy()->startOfMonth();
			$end_date   = $input->copy()->endOfMonth();
		} else {
			// This context not support any data.
			return;
		}

		// Get itemized array of events keyed by the "room_id" and divide by day.
		$events = $calendar->getEventsItemized(
			$start_date->subDay(), $end_date->addDay(), Calendar::BAT_DAILY
		);

		return array_map( function( $event ) {

			return $event[ Calendar::BAT_DAY ];

		}, $events );
	}

	/**
	 * Setup date data before prints.
	 *
	 * @param  Carbonate $date   Date instance.
	 * @param  string    $context Context from Calendar.
	 * @return void
	 */
	protected function setup_date( Carbonate $date, $context ) {
		$date_id = $date->toDateString();

		// If already exists classes, do nothing else.
		if ( isset( $this->_day_classes[ $date_id ] ) ) {
			return;
		}

		if ( $this->is_unavailable( $date ) ) {
			$this->_day_classes[ $date_id ] = 'unavailable';
		}

		if ( $this->get_option( 'changeover_day' ) ) {
			$prev_day = $date->copy()->subDay();
			if ( $this->is_unavailable( $date ) && ! $this->is_unavailable( $prev_day ) ) {
				$this->_day_classes[ $date_id ] = 'unavailable-start triangle-start';
			}

			$next_day = $date->copy()->addDay();
			if ( $this->is_unavailable( $date ) && ! $this->is_unavailable( $next_day ) ) {
				$this->_day_classes[ $next_day->toDateString() ] = 'unavailable-end triangle-end';
			}
		}
	}

	protected function is_available( Carbonate $day ) {
		$states = array_values( $this->get_state_of_day( $day ) );

		return in_array( AweBooking::STATE_AVAILABLE, $states );
	}

	protected function is_unavailable( Carbonate $date ) {
		return ! $this->is_available( $date );
	}

	/**
	 * Return state of each room in room-type on a day.
	 *
	 * @param  Carbonate $day Working day instance.
	 * @return array
	 */
	protected function get_state_of_day( Carbonate $day ) {
		$cacheid = $day->toDateString();

		// Found in the cache first.
		if ( isset( $this->_day_states[ $cacheid ] ) ) {
			return $this->_day_states[ $cacheid ];
		}

		$states = [];
		foreach ( $this->data as $room_id => $event ) {
			$states[ $room_id ] = $event[ $day->year ][ $day->month ][ 'd' . $day->day ];
		}

		$this->_day_states[ $cacheid ] = $states;
		return $states;
	}

	/**
	 * Get classess for date.
	 *
	 * @param  Carbonate $date Date instance.
	 * @return array
	 */
	protected function get_date_classes( Carbonate $date ) {
		$classes = parent::get_date_classes( $date );

		if ( isset( $this->_day_classes[ $date->toDateString() ] ) ) {
			$classes[] = $this->_day_classes[ $date->toDateString() ];
		}

		return $classes;
	}
}
