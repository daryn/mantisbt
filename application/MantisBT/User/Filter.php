<?php
namespace MantisBT\User;

# MantisBT - a php based bugtracking system

# @todo add new license text

/**
 *	User\Filter class
 * @package MantisBT
 * @subpackage classes
 */
class Filter {
	private $_sortColumn = 'username';
	private $_sortDirection = 'ASC';
	private $_hideInactive = false;
	private $_filter = false;
	private $_save = 1;

	private $_offset = 0;
	private $_pageNumber = 1;
	private $_pageCount = 1;
	private $_userCount = 0;
	private $_perPage = 50;

	public static function processManageUserFilter() {
		$t_user_table = db_get_table( 'user' );

		$f_sort_column = gpc_get_string( 'sort_column', 'username' );
		# Clean up the form variables
		if ( !db_field_exists( $f_sort_column, $t_user_table ) ) {
			$c_sort_column = 'username';
		} else {
			$c_sort_column = addslashes($f_sort_column);
		}

		$this->_sortColumn = $c_sort_column;
		$this->_sortDirection  = gpc_get_string( 'sort_direction', 'ASC' );
		$this->_hideInactive = gpc_get_bool( 'hide_inactive' );
		$this->_save = gpc_get_bool( 'save' );
		$this->_pageNumber = gpc_get_int( 'page_number', 1 );
		$this->_filter = utf8_strtoupper( gpc_get_string( 'filter', config_get( 'default_manage_user_prefix' ) ) );
	}

	public static function pageCount( $p_per_page ) {
		return ceil( $this->_userCount / $p_per_page);
	}

	public static function getValidPageNumber() {
		if ( $this->_pageNumber > $this->_pageCount ) {
			$this->_pageNumber = $this->_pageCount;
		}

		# Make sure $p_page_number isn't before the first page
		if ( $this->_pageNumber < 1 ) {
			$this->_pageNumber = 1;
		}
		return $this->_pageNumber;
	}

	public static function getFilterOffset() {
		return $this->_offset = ( ( this->_pageNumber - 1 ) * $this->_perPage  );
	}

	public static function getFilterLinks() {
		# build array for filter shortcuts
		$t_filter_url = "manage_user_page.php?sort_column=" . self::$sort_column . "&sort_direction=" . self::$sort_direction . "&save=1&hide_inactive=" . self::$hide_inactive . "&filter=";
		$t_selected = ( !self::$filter || 'ALL' == self::$filter ? true : false );
		$t_filter_links['ALL'] = array( 'label'=>lang_get( 'show_all_users' ), 'url'=>$t_filter_url . 'ALL', 'selected'=>$t_selected);

		for ( $i = 'A'; $i != 'AA'; $i++ ) {
			$t_selected = ( $i == self::$filter ? true : false );
			$t_filter_links[$i] = array( 'label'=>$i, 'url'=>$t_filter_url . $i, 'selected'=>$t_selected );
		}

		for ( $i = 0; $i <= 9; $i++ ) {
			$t_selected = ( $i === self::$filter ? true : false );
			$t_filter_links[$i] = array( 'label'=>$i, 'url'=>$t_filter_url . $i, 'selected'=>$t_selected );
		}

		$t_selected = ( 'UNUSED' == self::$filter ? true : false );
		$t_filter_links['UNUSED'] = array( 'label'=>lang_get( 'users_unused' ), 'url'=>$t_filter_url . 'UNUSED', 'selected'=>$t_selected );
		$t_selected = ( 'NEW' == self::$filter ? true : false );
		$t_filter_links['NEW'] = array( 'label'=>lang_get( 'users_new' ), 'url'=>$t_filter_url . 'NEW', 'selected'=>$t_selected );

		return $t_filter_links;
	}

	/**
	 *	Build an array of data for sorting the user columns
	 *	@return array data for sorting the user columns
	 */
	public static function getFilterSortLinks() {

		$t_page = "manage_user_page.php?";

		$t_sort_fields['username'] = array();
		$t_sort_fields['realname'] = array();
		$t_sort_fields['email'] = array();
		$t_sort_fields['access_level'] = array();
		$t_sort_fields['enabled'] = array();
		$t_sort_fields['protected'] = array();
		$t_sort_fields['date_created'] = array();
		$t_sort_fields['last_visit'] = array();

		foreach( $t_sort_fields AS $t_key=>$t_value ) {
			$t_label = lang_get( $t_key );
			if( self::$sort_column == $t_key ) {
				# this is the same as the previous sort, swap directions
				$t_sort_direction = ( self::$sort_direction == 'ASC' ? 'DESC' : 'ASC' );
				$t_icon_path = config_get( 'icon_path' );
				$t_sort_icon_arr = config_get( 'sort_icon_arr' );

				$t_direction = ( self::$sort_direction == 'ASC' ? ASCENDING : DESCENDING );

				$t_sort_icon = $t_icon_path . $t_sort_icon_arr[$t_direction];
				$t_sort_alt = $t_label . ' ' . self::$sort_direction;
			} else {
				# set the default direction
				$t_sort_direction = 'ASC';
				$t_sort_icon = false;
			}
			$t_url = "manage_user_page.php?sort_column=" . $t_key . "&sort_direction=" . $t_sort_direction . "&save=1&hide_inactive=" . self::$hide_inactive . "&filter=" . self::$filter;
			$t_sort_fields[$t_key] = array( 'field'=>$t_key, 'url'=>$t_url, 'label'=>$t_label, 'icon_url'=>$t_sort_icon, 'sort_alt'=>$t_sort_alt );
		}
		return $t_sort_fields;
	}

	/**
	 *	@todo This should probably be generalized to handle any pager but
	 *		this is basically an example for generating paging links for a template
	 */
	public static function getManageUserPager( $p_start=1 ) {
		# Check if we have more than one page, otherwise return without doing anything.
		if( self::$page_count - 1 < 1 ) {
			return false;
		}

		# Get localized strings
		$t_first_label = lang_get( 'first' );
		$t_last_label = lang_get( 'last' );
		$t_prev_label = lang_get( 'prev' );
		$t_next_label = lang_get( 'next' );
		$t_page_link_count = 10;
		$t_page = 'manage_user_page.php';
		if( self::$filter !== 0 ) {
			$t_url = "$t_page?filter=" . self::$filter . "&page_number=";
		} else {
			$t_url = "$t_page?page_number=";
		}

		if( self::$page_number == 1 ) {
			$t_page_link_arr['first'] = array('url'=>false,'label'=>$t_first_label  );
		} else {
			$t_page_link_arr['first'] = array('url'=>$t_url . 1,'label'=>$t_first_label  );
		}
		$t_page_link_arr['prev'] = array('url'=>( self::$page_number - 1 ),'label'=>$t_prev_label  );

		# Page numbers ...
		$t_first_page = max( $p_start, self::$page_number - $t_page_link_count / 2 );
		$t_first_page = min( $t_first_page, self::$page_count - $t_page_link_count );
		$t_first_page = max( $t_first_page, $p_start );

		if( self::$page_count > 10 && $t_first_page > 1 ) {
			$t_page_link_arr['separator1'] = array('url'=>false,'label'=>' ... ');
		}

		$t_last_page = $t_first_page + $t_page_link_count;
		$t_last_page = min( $t_last_page, self::$page_count);

		for( $i = $t_first_page;$i <= $t_last_page;$i++ ) {
			if( $i == self::$page_number ) {
				$t_page_link_arr[$i] = array( 'url'=>false, 'label'=>$i ); # current. no url
			} else {
				$t_page_link_arr[$i] = array( 'url'=>$t_url . $i, 'label'=>$i );
			}
		}

		if( self::$page_count > 10 && $t_last_page < self::$page_count ) {
			$t_page_link_arr['separator2'] = array('url'=>false,'label'=>' ... ');
		}

		# Next and Last links
		if( self::$page_number < self::$page_count ) {
			$t_page_link_arr['next'] = array('url'=>$t_url. ( self::$page_number + 1 ),'label'=>$t_next_label  );
			$t_page_link_arr['last'] = array('url'=>$t_url . self::$page_count ,'label'=>$t_last_label  );
		} else {
			# no link. just print the next label
			$t_page_link_arr['next'] = array('url'=>false,'label'=>$t_next_label  );
			$t_page_link_arr['last'] = array('url'=>false,'label'=>$t_last_label  );
		}


		return $t_page_link_arr;
	}
} # End of user class
