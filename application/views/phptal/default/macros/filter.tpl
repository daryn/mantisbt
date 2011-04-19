<div metal:define-macro="filter">
<!-- function filter_draw_selection_area( $p_page_number, $p_for_screen = true ) {
	collapse_open( 'filter' );
	filter_draw_selection_area2( $p_page_number, $p_for_screen, true );
	collapse_closed( 'filter' );
	filter_draw_selection_area2( $p_page_number, $p_for_screen, false );
	collapse_end( 'filter' );
} -->

<!-- function filter_draw_selection_area2( $p_page_number, $p_for_screen = true, $p_expanded = true ) {
	$t_form_name_suffix = $p_expanded ? '_open' : '_closed';

	$t_filter = current_user_get_bug_filter();
	$t_filter = filter_ensure_valid_filter( $t_filter );
	$t_project_id = helper_get_current_project();
	$t_page_number = (int) $p_page_number;

	$t_view_type = $t_filter['_view_type'];

	$t_tdclass = 'small-caption';
	$t_trclass = 'row-category2';
	$t_action = 'view_all_set.php?f=3';

	if( $p_for_screen == false ) {
		$t_tdclass = 'print';
		$t_trclass = '';
		$t_action = 'view_all_set.php';
	}
	?>
-->
	<div class="filter-box">
		<form method="post" tal:attributes="name string:filters{$form_name_suffix};id string:filters_form{$form_name_suffix};action action" tal:comment="CSRF protection not required here - form does not result in modifications">
		<input type="hidden" name="type" value="1" />
		<input tal:condition="not: for_screen" type="hidden" name="print" value="1" />
		<input tal:condition="not: for_screen" type="hidden" name="offset" value="0" />
		<input type="hidden" name="page_number" tal:attributes="value page_number" />
		<input type="hidden" name="view_type" tal:attributes="value view_type" />
		$t_filter_cols = config_get( 'filter_custom_fields_per_row' );
		<table tal:condition="expanded" width="100%" cellspacing="1">

		$t_dynamic_filter_expander_class = ( config_get( 'use_javascript' ) && config_get( 'use_dynamic_filters' ) ) ? ' class="dynamic-filter-expander"' : '';

		<tr tal:attributes="class trclass">
			<td class="small-caption">
				<a id="reporter_id_filter" tal:attributes="href filters_url . FILTER_PROPERTY_REPORTER_ID[];class dynamic_filter_expander_class|nothing" tal:content="lang/reporter_label">Reporter:</a>
			</td>
			<td class="small-caption">
				<a id="user_monitor_filter" tal:attributes="href $t_filters_url . FILTER_PROPERTY_MONITOR_USER_ID . '[]';class dynamic_filter_expander_class|nothing" tal:content="lang/monitored_by_label">Monitored by label:</a>
			</td>
			<td class="small-caption">
				<a id="handler_id_filter" tal:attributes="href $t_filters_url . FILTER_PROPERTY_HANDLER_ID . '[]';class dynamic_filter_expander_class|nothing" tal:content="lang/assigned_to_label">Assigned To:</a>
			</td>
			<td colspan="2" class="small-caption">
				<a id="show_category_filter" tal:attributes="href $t_filters_url . FILTER_PROPERTY_CATEGORY_ID . '[]';class dynamic_filter_expander_class|nothing" tal:content="lang/category_label">Category:</a>
			</td>
			<td class="small-caption">
				<a id="show_severity_filter" tal:attributes="href $t_filters_url . FILTER_PROPERTY_SEVERITY . '[]';class dynamic_filter_expander_class|nothing" tal:content="lang/severity_label">Severity:</a>
			</td>
			<td class="small-caption">
				<a id="show_resolution_filter" tal:attributes="href $t_filters_url . FILTER_PROPERTY_RESOLUTION . '[]';class dynamic_filter_expander_class|nothing" tal:content="lang/resolution_label">Resolution:</a>
			</td>
			<td class="small-caption">
				<a tal:condition="config/enable_profiles" id="show_profile_filter" tal:attributes="href filters_url . FILTER_PROPERTY_PROFILE_ID . '[]';class dynamic_filter_expander_class|nothing" tal:content="lang/profile_label">Profile:</a>
			</td>
			<td tal:condition="filter_cols gt 8" class="small-caption" tal:attributes="colspan $t_filter_cols - 8 )">&#160;</td>
		</tr>

		<tr class="row-1">
			<td class="small-caption" id="reporter_id_filter_target">
<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_REPORTER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_REPORTER_ID] as $t_current ) {
				$t_this_name = '';
				echo '<input type="hidden" name="', FILTER_PROPERTY_REPORTER_ID, '[]" value="', $t_current, '" />';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				}
				else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else if( filter_field_is_none( $t_current ) ) {
					$t_this_name = lang_get( 'none' );
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_name;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo string_display( $t_output );
			}
		}
		?>
			</td>
			<td class="small-caption" id="user_monitor_filter_target">
				<?php
					$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_MONITOR_USER_ID, '[]" value="', $t_current, '" />';
				$t_this_name = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				}
				else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_name;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo string_display( $t_output );
			}
		}
		?>
			</td>
			<td class="small-caption" id="handler_id_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_HANDLER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_HANDLER_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_HANDLER_ID, '[]" value="', $t_current, '" />';
				$t_this_name = '';
				if( filter_field_is_none( $t_current ) ) {
					$t_this_name = lang_get( 'none' );
				} else if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_name;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo string_display( $t_output );
			}
		}
		?>
			</td>
			<td colspan="2" class="small-caption" id="show_category_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_CATEGORY_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_CATEGORY_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_CATEGORY_ID, '[]" value="', $t_current, '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = string_display( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<td class="small-caption" id="show_severity_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_SEVERITY] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_SEVERITY] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_SEVERITY, '[]" value="', $t_current, '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'severity', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<td class="small-caption" id="show_resolution_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_RESOLUTION] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_RESOLUTION] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_RESOLUTION, '[]" value="', $t_current, '" />';
										$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'resolution', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
			<td class="small-caption" id="show_profile_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_PROFILE_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_PROFILE_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_PROFILE_ID, '[]" value="', $t_current, '" />';
										$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_profile = profile_get_row_direct( $t_current );

					$t_this_string = "${t_profile['platform']} ${t_profile['os']} ${t_profile['os_build']}";
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<?php } else { ?>
				<td></td>
			<?php }
				  if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
			</tr>

		<tr <?php echo "class=\"" . $t_trclass . "\"";?>>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_STATUS . '[]';?>" id="show_status_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'status_label' )?></a>
			</td>
			<td class="small-caption">
				<?php if( 'simple' == $t_view_type ) {?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_HIDE_STATUS . '[]';?>" id="hide_status_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'hide_status_label' )?></a>
				<?php
		}?>
			</td>
			<td class="small-caption">
			<?php if ( $t_show_build ) { ?>
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_BUILD . '[]';?>" id="show_build_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'product_build_label' )?></a>
			<?php } ?>
			</td>
			<?php if( $t_show_product_version ) {?>
			<td colspan="2" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_VERSION . '[]';?>" id="show_version_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'product_version_label' )?></a>
			</td>
			<td colspan="1" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_FIXED_IN_VERSION . '[]';?>" id="show_fixed_in_version_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'fixed_in_version_label' )?></a>
			</td>
			<?php
		} else {?>
			<td colspan="2" class="small-caption">
				&#160;
			</td>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
			<?php
		}?>
			<td colspan="1" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_PRIORITY . '[]';?>" id="show_priority_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'priority_label' )?></a>
			</td>
			<?php if( $t_show_product_version ) {?>
			<td colspan="1" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_TARGET_VERSION . '[]';?>" id="show_target_version_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'target_version_label' )?></a>
			</td>
			<?php
			} else {?>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
			<?php
			}
			if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 7 ) . '">&#160;</td>';
		}?>
		</tr>

		<tr class="row-1">
			<td class="small-caption" id="show_status_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_STATUS] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_STATUS] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_STATUS, '[]" value="', $t_current, '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'status', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<td class="small-caption" id="hide_status_filter_target">
							<?php
								if( 'simple' == $t_view_type ) {
			$t_output = '';
			$t_none_found = false;
			if( count( $t_filter[FILTER_PROPERTY_HIDE_STATUS] ) == 0 ) {
				echo lang_get( 'none' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_HIDE_STATUS] as $t_current ) {
					echo '<input type="hidden" name="', FILTER_PROPERTY_HIDE_STATUS, '[]" value="', $t_current, '" />';
					$t_this_string = '';
					if( filter_field_is_none( $t_current ) ) {
						$t_none_found = true;
					} else {
						$t_this_string = get_enum_element( 'status', $t_current );
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . $t_this_string;
				}
				$t_hide_status_post = '';
				if( count( $t_filter[FILTER_PROPERTY_HIDE_STATUS] ) == 1 ) {
					$t_hide_status_post = ' (' . lang_get( 'and_above' ) . ')';
				}
				if( true == $t_none_found ) {
					echo lang_get( 'none' );
				} else {
					echo $t_output . $t_hide_status_post;
				}
			}
		}
		?>
			</td>
			<?php if ( $t_show_build ) { ?>
			<td class="small-caption" id="show_build_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_BUILD] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_BUILD] as $t_current ) {
				$t_current = stripslashes( $t_current );
				echo '<input type="hidden" name="', FILTER_PROPERTY_BUILD, '[]" value="', string_display( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_none( $t_current ) ) {
					$t_this_string = lang_get( 'none' );
				} else {
					$t_this_string = string_display( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<?php } else { ?>
			<td class="small-caption"></td>
			<?php }
				if( $t_show_product_version ) {
			?>
			<td colspan="2" class="small-caption" id="show_version_filter_target">
							<?php
								$t_output = '';
			$t_any_found = false;
			if( count( $t_filter[FILTER_PROPERTY_VERSION] ) == 0 ) {
				echo lang_get( 'any' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_VERSION] as $t_current ) {
					$t_current = stripslashes( $t_current );
					echo '<input type="hidden" name="', FILTER_PROPERTY_VERSION, '[]" value="', string_display( $t_current ), '" />';
					$t_this_string = '';
					if( filter_field_is_any( $t_current ) ) {
						$t_any_found = true;
					}
					else if( filter_field_is_none( $t_current ) ) {
						$t_this_string = lang_get( 'none' );
					} else {
						$t_this_string = string_display( $t_current );
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . $t_this_string;
				}
				if( true == $t_any_found ) {
					echo lang_get( 'any' );
				} else {
					echo $t_output;
				}
			}
			?>
			</td>
			<td colspan="1" class="small-caption" id="show_fixed_in_version_filter_target">
							<?php
								$t_output = '';
			$t_any_found = false;
			if( count( $t_filter[FILTER_PROPERTY_FIXED_IN_VERSION] ) == 0 ) {
				echo lang_get( 'any' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_FIXED_IN_VERSION] as $t_current ) {
					$t_current = stripslashes( $t_current );
					echo '<input type="hidden" name="', FILTER_PROPERTY_FIXED_IN_VERSION, '[]" value="', string_display( $t_current ), '" />';
					$t_this_string = '';
					if( filter_field_is_any( $t_current ) ) {
						$t_any_found = true;
					} else if( filter_field_is_none( $t_current ) ) {
						$t_this_string = lang_get( 'none' );
					} else {
						$t_this_string = string_display( $t_current );
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . $t_this_string;
				}
				if( true == $t_any_found ) {
					echo lang_get( 'any' );
				} else {
					echo $t_output;
				}
			}
			?>
			</td>
			<?php
		} else {?>
			<td colspan="2" class="small-caption">
				&#160;
			</td>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
			<?php
		}?>
			<td colspan="1" class="small-caption" id="show_priority_filter_target">
              <?php
							  $t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_PRIORITY] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_PRIORITY] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_PRIORITY, '[]" value="', $t_current, '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'priority', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
		</td>
		<?php if( $t_show_product_version ) { ?>
		<td colspan="1" class="small-caption" id="show_target_version_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_TARGET_VERSION] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_TARGET_VERSION] as $t_current ) {
				$t_current = stripslashes( $t_current );
				echo '<input type="hidden" name="', FILTER_PROPERTY_TARGET_VERSION, '[]" value="', string_display( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_none( $t_current ) ) {
					$t_this_string = lang_get( 'none' );
				} else {
					$t_this_string = string_display( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_string;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
		<?php } else { ?>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
		<?php }

		if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 7 ) . '">&#160;</td>';
		}?>

		</tr>

		<tr <?php echo "class=\"" . $t_trclass . "\"";?>>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_ISSUES_PER_PAGE;?>" id="per_page_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'show_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_VIEW_STATE;?>" id="view_state_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'view_status_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_STICKY;?>" id="sticky_issues_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'sticky_label' )?></a>
			</td>
			<td class="small-caption" colspan="2">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_HIGHLIGHT_CHANGED;?>" id="highlight_changed_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'changed_label' )?></a>
			</td>
			<td class="small-caption" >
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_FILTER_BY_DATE;?>" id="do_filter_by_date_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'use_date_filters_label' )?></a>
			</td>
			<td class="small-caption" colspan="2">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_RELATIONSHIP_TYPE;?>" id="relationship_type_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'bug_relationships_label' )?></a>
			</td>
			<?php if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
		</tr>
		<tr class="row-1">
			<td class="small-caption" id="per_page_filter_target">
				<?php
					echo( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] == 0 ) ? lang_get( 'all' ) : $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE];
		echo '<input type="hidden" name="', FILTER_PROPERTY_ISSUES_PER_PAGE, '" value="', $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE], '" />';
		?>
			</td>
			<td class="small-caption" id="view_state_filter_target">
				<?php
				if( VS_PUBLIC === $t_filter[FILTER_PROPERTY_VIEW_STATE] ) {
			echo lang_get( 'public' );
		} else if( VS_PRIVATE === $t_filter[FILTER_PROPERTY_VIEW_STATE] ) {
			echo lang_get( 'private' );
		} else {
			echo lang_get( 'any' );
			$t_filter[FILTER_PROPERTY_VIEW_STATE] = META_FILTER_ANY;
		}
		echo '<input type="hidden" name="', FILTER_PROPERTY_VIEW_STATE, '" value="', $t_filter[FILTER_PROPERTY_VIEW_STATE], '" />';
		?>
			</td>
			<td class="small-caption" id="sticky_issues_filter_target">
				<?php
					$t_sticky_filter_state = gpc_string_to_bool( $t_filter[FILTER_PROPERTY_STICKY] );
		print( $t_sticky_filter_state ? lang_get( 'yes' ) : lang_get( 'no' ) );
		?>
				<input type="hidden" name="sticky_issues" value="<?php echo $t_sticky_filter_state ? 'on' : 'off';?>" />
			</td>
			<td class="small-caption" colspan="2" id="highlight_changed_filter_target">
				<?php
					echo $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED];
		echo '<input type="hidden" name="', FILTER_PROPERTY_HIGHLIGHT_CHANGED, '" value="', $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED], '" />';
		?>
			</td>
			<td class="small-caption" id="do_filter_by_date_filter_target">
		<?php
		if( 'on' == $t_filter[FILTER_PROPERTY_FILTER_BY_DATE] ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_FILTER_BY_DATE, '" value="', $t_filter[FILTER_PROPERTY_FILTER_BY_DATE], '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_START_MONTH, '" value="', $t_filter[FILTER_PROPERTY_START_MONTH], '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_START_DAY, '" value="', $t_filter[FILTER_PROPERTY_START_DAY], '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_START_YEAR, '" value="', $t_filter[FILTER_PROPERTY_START_YEAR], '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_END_MONTH, '" value="', $t_filter[FILTER_PROPERTY_END_MONTH], '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_END_DAY, '" value="', $t_filter[FILTER_PROPERTY_END_DAY], '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_END_YEAR, '" value="', $t_filter[FILTER_PROPERTY_END_YEAR], '" />';

			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
			$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_START_MONTH], $t_filter[FILTER_PROPERTY_START_DAY], $t_filter[FILTER_PROPERTY_START_YEAR] );
			foreach( $t_chars as $t_char ) {
				if( strcasecmp( $t_char, "M" ) == 0 ) {
					echo ' ';
					echo date( 'F', $t_time );
				}
				if( strcasecmp( $t_char, "D" ) == 0 ) {
					echo ' ';
					echo date( 'd', $t_time );
				}
				if( strcasecmp( $t_char, "Y" ) == 0 ) {
					echo ' ';
					echo date( 'Y', $t_time );
				}
			}

			echo ' - ';

			$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_END_MONTH], $t_filter[FILTER_PROPERTY_END_DAY], $t_filter[FILTER_PROPERTY_END_YEAR] );
			foreach( $t_chars as $t_char ) {
				if( strcasecmp( $t_char, "M" ) == 0 ) {
					echo ' ';
					echo date( 'F', $t_time );
				}
				if( strcasecmp( $t_char, "D" ) == 0 ) {
					echo ' ';
					echo date( 'd', $t_time );
				}
				if( strcasecmp( $t_char, "Y" ) == 0 ) {
					echo ' ';
					echo date( 'Y', $t_time );
				}
			}
		} else {
			echo lang_get( 'no' );
		}
		?>
			</td>

			<td class="small-caption" colspan="2" id="relationship_type_filter_target">
							<?php
								echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_TYPE, '" value="', $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE], '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_BUG, '" value="', $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG], '" />';
		$c_rel_type = $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
		$c_rel_bug = $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG];
		if( -1 == $c_rel_type || 0 == $c_rel_bug ) {
			echo lang_get( 'any' );
		} else {
			echo relationship_get_description_for_history( $c_rel_type ) . ' ' . $c_rel_bug;
		}

		?>
			</td>
			<?php if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
		</tr>
		<tr <?php echo "class=\"" . $t_trclass . "\"";?>>
			<td class="small-caption">
				<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_PLATFORM;?>" id="platform_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'platform_label' )?></a>
				<?php } ?>
			</td>
			<td class="small-caption">
				<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_OS;?>" id="os_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'os_label' )?></a>
				<?php } ?>
			</td>
			<td class="small-caption">
				<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_OS_BUILD;?>" id="os_build_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'os_version_label' )?></a>
				<?php } ?>
			</td>
			<td class="small-caption" colspan="5">
				<?php if ( access_has_global_level( config_get( 'tag_view_threshold' ) ) ) { ?>
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_TAG_STRING;?>" id="tag_string_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'tags_label' )?></a>
				<?php } ?>
			</td>
			<?php if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
		</tr>
		<tr class="row-1">
			<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
			<td class="small-caption" id="platform_filter_target">
				<?php
					print_multivalue_field( FILTER_PROPERTY_PLATFORM, $t_filter[FILTER_PROPERTY_PLATFORM] );
		?>
			</td>
			<td class="small-caption" id="os_filter_target">
				<?php
					print_multivalue_field( FILTER_PROPERTY_OS, $t_filter[FILTER_PROPERTY_OS] );
		?>
			</td>
			<td class="small-caption" id="os_build_filter_target">
				<?php
					print_multivalue_field( FILTER_PROPERTY_OS_BUILD, $t_filter[FILTER_PROPERTY_OS_BUILD] );
		?>
			</td>
			<?php } else {?>
				<td colspan="3">&#160;</td>
			<?php } ?>

			<td class="small-caption" id="tag_string_filter_target" colspan="5">
				<?php
					$t_tag_string = $t_filter[FILTER_PROPERTY_TAG_STRING];
		if( $t_filter[FILTER_PROPERTY_TAG_SELECT] != 0 && tag_exists( $t_filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
			$t_tag_string .= ( is_blank( $t_tag_string ) ? '' : config_get( 'tag_separator' ) );
			$t_tag_string .= tag_get_field( $t_filter[FILTER_PROPERTY_TAG_SELECT], 'name' );
		}
		echo string_html_entities( $t_tag_string );
		echo '<input type="hidden" name="', FILTER_PROPERTY_TAG_STRING, '" value="', string_attribute( $t_tag_string ), '" />';
		?>
			</td>
		</tr>
		<?php

		# get plugin filters
		$t_plugin_filters = filter_get_plugin_filters();
		$t_plugin_filter_links = array();
		$t_plugin_filter_fields = array();
		$t_column_count_by_row = array();
		$t_row = 0;
		foreach( $t_plugin_filters AS $t_field_name=>$t_filter_object ) {
			# be sure the colspan is an integer
			$t_colspan = (int) $t_filter_object->colspan;

			# prevent silliness.
			if( $t_colspan < 0 ) {
				$t_colspan = abs( $t_colspan );
			} else if( $t_colspan > $t_filter_cols ) {
				$t_colspan = $t_filter_cols;
			} else if( $t_colspan == 0 ) {
				$t_colspan = 1;
			}
			# the row may already have elements in it. find out.
			$t_columns_available = $t_filter_cols - $t_column_count_by_row[$t_row];
			if( $t_columns_available == 0 ) {
				$t_row++;
			}

			# see if there is room in the current row
			if( $t_columns_available >= $t_colspan ) {
				$t_assigned_row = $t_row;
				$t_column_count_by_row[$t_row] += $t_colspan;
			} else {
				$t_is_assigned = false;
				# find a row with space
				foreach( $t_column_count_by_row AS $t_row_num=>$t_col_count ) {
					if( $t_colspan <= ( $t_filter_cols - $t_col_count ) ) {
						$t_assigned_row = $t_row_num;
						$t_column_count_by_row[$t_row_num] += $t_colspan;
						$t_is_assigned = true;
						break;
					}
				}
				if( !$t_is_assigned ) {
					# no space was found in existing rows. Add a new row for it.
					$t_assigned_row = count( $t_plugin_filter_links );
					$t_column_count_by_row[$t_assigned_row] = $t_colspan;
				}
			}
			$t_colspan_attr = ( $t_colspan > 1 ? 'colspan="' . $t_colspan . '" ' : '' );
			$t_plugin_filter_links[$t_assigned_row][] = '<td ' . $t_colspan_attr . 'class="small-caption"> <a href="' . string_attribute( $t_filters_url . $t_field_name ) .
				'" id="' . $t_field_name . '_filter">' . string_display_line( $t_filter_object->title ) . '</a> </td>';
			$t_values = '<td ' . $t_colspan_attr . 'class="small-caption" id="' . $t_field_name . '_filter_target"> ';

			if ( !isset( $t_filter[ $t_field_name ] ) ) {
				$t_values .= lang_get( 'any' );
			} else {
				switch( $t_filter_object->type ) {
					case FILTER_TYPE_STRING:
					case FILTER_TYPE_INT:
						if ( filter_field_is_any( $t_filter[ $t_field_name ] ) ) {
							$t_values .= lang_get( 'any' );
						} else {
							$t_values .= string_display( $t_filter[ $t_field_name ] );
						}
						$t_values .= '<input type="hidden" name="' . string_attribute( $t_field_name ) . '" value="' . string_attribute( $t_filter[ $t_field_name ] ) . '"/>';
						break;

					case FILTER_TYPE_BOOLEAN:
						$t_values .= string_display( $t_filter_object->display( (bool)$t_filter[ $t_field_name ] ) );
						$t_values .= '<input type="hidden" name="' . string_attribute( $t_field_name ) . '" value="' . (bool)$t_filter[ $t_field_name ] . '"/>';
						break;

					case FILTER_TYPE_MULTI_STRING:
					case FILTER_TYPE_MULTI_INT:
						$t_first = true;
						$t_output = '';

						if ( !is_array( $t_filter[ $t_field_name ] ) ) {
							$t_filter[ $t_field_name ] = array( $t_filter[ $t_field_name ] );
						}

						foreach( $t_filter[ $t_field_name ] as $t_current ) {
							if ( filter_field_is_any( $t_current ) ) {
								$t_output .= lang_get( 'any' );
							} else {
								$t_output .= ( $t_first ? '' : '<br/>' ) . string_display( $t_filter_object->display( $t_current ) );
								$t_first = false;
							}
							$t_values .= '<input type="hidden" name="' . string_attribute( $t_field_name ) . '[]" value="' . string_attribute( $t_current ) . '"/>';
						}

						$t_values .= $t_output;
						break;
				}
			}

			$t_values .= '</td>';

			$t_plugin_filter_fields[$t_assigned_row][] = $t_values;
		}

		$t_row_count = count( $t_plugin_filter_links );
		for( $i=0; $i<$t_row_count; $i++ ) {
			if( $t_column_count_by_row[$i] < $t_filter_cols ) {
				$t_plugin_filter_links[$i][] = '<td class="small-caption" colspan="' . ( $t_filter_cols - $t_column_count_by_row[$i] ) . '">&#160;</td>';
				$t_plugin_filter_fields[$i][] = '<td class="small-caption" colspan="' . ( $t_filter_cols - $t_column_count_by_row[$i] ) . '">&#160;</td>';
			}
			$t_links_row = "\n\t\t" . join( "\n\t\t", $t_plugin_filter_links[$i] );
			$t_values_row = "\n\t\t" . join( "\n\t\t", $t_plugin_filter_fields[$i] );
			echo "\n\t" . '<tr class="', $t_trclass, '">', $t_links_row, "\n\t</tr>";
			echo "\n\t" . '<tr class="row-1">', $t_values_row, "\n\t</tr>\n\t";
		}

		if( ON == config_get( 'filter_by_custom_fields' ) ) {

			# -- Custom Field Searching --

			if( count( $t_accessible_custom_fields_ids ) > 0 ) {
				$t_per_row = config_get( 'filter_custom_fields_per_row' );
				$t_num_fields = count( $t_accessible_custom_fields_ids );
				$t_row_idx = 0;
				$t_col_idx = 0;

				$t_fields = '';
				$t_values = '';

				for( $i = 0;$i < $t_num_fields;$i++ ) {
					if( $t_col_idx == 0 ) {
						$t_fields = '<tr class="' . $t_trclass . '">';
						$t_values = '<tr class="row-1">';
					}

					if( isset( $t_accessible_custom_fields_names[$i] ) ) {
						$t_fields .= '<td class="small-caption"> ';
						$t_fields .= '<a href="' . $t_filters_url . 'custom_field_' . $t_accessible_custom_fields_ids[$i] . '[]" id="custom_field_' . $t_accessible_custom_fields_ids[$i] . '_filter"' . $t_dynamic_filter_expander_class . '>';
						$t_fields .= string_display( lang_get_defaulted( $t_accessible_custom_fields_names[$i] ) );
						$t_fields .= '</a> </td> ';
					}
					$t_output = '';
					$t_any_found = false;

					$t_values .= '<td class="small-caption" id="custom_field_' . $t_accessible_custom_fields_ids[$i] . '_filter_target"> ';
					if( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] ) ) {
						$t_values .= lang_get( 'any' );
					} else {
						if( $t_accessible_custom_fields_types[$i] == CUSTOM_FIELD_TYPE_DATE ) {
							$t_short_date_format = config_get( 'short_date_format' );
							if( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] ) ) {
								$t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] = 0;
							}
							$t_start = date( $t_short_date_format, $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] );

							if( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] ) ) {
								$t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] = 0;
							}
							$t_end = date( $t_short_date_format, $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] );
							switch( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][0] ) {
								case CUSTOM_FIELD_DATE_ANY:
									$t_values .= lang_get( 'any' );
									break;
								case CUSTOM_FIELD_DATE_NONE:
									$t_values .= lang_get( 'none' );
									break;
								case CUSTOM_FIELD_DATE_BETWEEN:
									$t_values .= lang_get( 'between_date' ) . '<br />';
									$t_values .= $t_start . '<br />' . $t_end;
									break;
								case CUSTOM_FIELD_DATE_ONORBEFORE:
									$t_values .= lang_get( 'on_or_before_date' ) . '<br />';
									$t_values .= $t_end;
									break;
								case CUSTOM_FIELD_DATE_BEFORE:
									$t_values .= lang_get( 'before_date' ) . '<br />';
									$t_values .= $t_end;
									break;
								case CUSTOM_FIELD_DATE_ON:
									$t_values .= lang_get( 'on_date' ) . '<br />';
									$t_values .= $t_start;
									break;
								case CUSTOM_FIELD_DATE_AFTER:
									$t_values .= lang_get( 'after_date' ) . '<br />';
									$t_values .= $t_start;
									break;
								case CUSTOM_FIELD_DATE_ONORAFTER:
									$t_values .= lang_get( 'on_or_after_date' ) . '<br />';
									$t_values .= $t_start;
									break;
							}
						} else {
							$t_first_flag = true;
							foreach( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] as $t_current ) {
								$t_current = stripslashes( $t_current );
								$t_this_string = '';
								if( filter_field_is_any( $t_current ) ) {
									$t_any_found = true;
								} else if( filter_field_is_none( $t_current ) ) {
									$t_this_string = lang_get( 'none' );
								} else {
									$t_this_string = string_display( $t_current );
								}

								if( $t_first_flag != true ) {
									$t_output = $t_output . '<br />';
								} else {
									$t_first_flag = false;
								}

								$t_output = $t_output . $t_this_string;
								$t_values .= '<input type="hidden" name="custom_field_' . $t_accessible_custom_fields_ids[$i] . '[]" value="' . string_display( $t_current ) . '" />';
							}
						}

						if( true == $t_any_found ) {
							$t_values .= lang_get( 'any' );
						} else {
							$t_values .= $t_output;
						}
					}
					$t_values .= ' </td>';

					$t_col_idx++;

					if( $t_col_idx == $t_per_row ) {
						if( $t_filter_cols > $t_per_row ) {
							$t_fields .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
							$t_values .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
						}

						$t_fields .= '</tr>' . "\n";
						$t_values .= '</tr>' . "\n";

						echo $t_fields;
						echo $t_values;

						$t_col_idx = 0;
						$t_row_idx++;
					}
				}

				if( $t_col_idx > 0 ) {
					if( $t_col_idx < $t_per_row ) {
						$t_fields .= '<td colspan="' . ( $t_per_row - $t_col_idx ) . '">&#160;</td> ';
						$t_values .= '<td colspan="' . ( $t_per_row - $t_col_idx ) . '">&#160;</td> ';
					}

					if( $t_filter_cols > $t_per_row ) {
						$t_fields .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
						$t_values .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
					}

					$t_fields .= '</tr>' . "\n";
					$t_values .= '</tr>' . "\n";

					echo $t_fields;
					echo $t_values;
				}
			}
		}
		?>
		<tr class="row-1">
			<td class="small-caption category2">
                <a href="<?php echo $t_filters_url . FILTER_PROPERTY_NOTE_USER_ID;?>" id="note_user_id_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'note_user_id_label' )?></a>
            </td>
            <td class="small-caption" id="note_user_id_filter_target">
                <?php
                    $t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_NOTE_USER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_NOTE_USER_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_NOTE_USER_ID, '[]" value="', $t_current, '" />';
				$t_this_name = '';
				if( filter_field_is_none( $t_current ) ) {
					$t_this_name = lang_get( 'none' );
				} else if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . $t_this_name;
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo string_display( $t_output );
			}
		}
		?>
            </td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . 'show_sort';?>" id="show_sort_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'sort_label' )?></a>
			</td>
			<td class="small-caption" id="show_sort_filter_target">
				<?php
					$t_sort_fields = explode( ',', $t_filter[FILTER_PROPERTY_SORT_FIELD_NAME] );
		$t_dir_fields = explode( ',', $t_filter[FILTER_PROPERTY_SORT_DIRECTION] );

		for( $i = 0;$i < 2;$i++ ) {
			if( isset( $t_sort_fields[$i] ) ) {
				if( 0 < $i ) {
					echo ', ';
				}
				$t_sort = $t_sort_fields[$i];
				if( strpos( $t_sort, 'custom_' ) === 0 ) {
					$t_field_name = string_display( lang_get_defaulted( utf8_substr( $t_sort, utf8_strlen( 'custom_' ) ) ) );
				} else {
					$t_field_name = string_get_field_name( $t_sort );
				}

				echo $t_field_name . ' ' . lang_get( 'bugnote_order_' . utf8_strtolower( $t_dir_fields[$i] ) );
				<input type="hidden" name="', FILTER_PROPERTY_SORT_FIELD_NAME, '_', $i, '" value="', $t_sort_fields[$i], '" />
				<input type="hidden" name="', FILTER_PROPERTY_SORT_DIRECTION, '_', $i, '" value="', $t_dir_fields[$i], '" />
			}
		}
		?>
			</td>
			<tal:block tal:condition="is_advanced">
				<td class="small-caption" colspan="2">
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_PROJECT_ID;?>" id="project_id_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'email_project_label' )?></a>
				</td>
				<td class="small-caption" id="project_id_filter_target">
				$t_output = '';
			if( count( $t_filter[FILTER_PROPERTY_PROJECT_ID] ) == 0 ) {
				echo lang_get( 'current' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_PROJECT_ID] as $t_current ) {
					echo '<input type="hidden" name="', FILTER_PROPERTY_PROJECT_ID, '[]" value="', $t_current, '" />';
					$t_this_name = '';
					if( META_FILTER_CURRENT == $t_current ) {
						$t_this_name = lang_get( 'current' );
					} else {
						$t_this_name = project_get_name( $t_current, false );
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . string_display_line( $t_this_name );
				}
				echo $t_output;
			}
			</td>
			<td tal:condition="filter_cols gt 6" class="small-caption" colspan="' . ( $t_filter_cols - 5 ) . '">&#160;</td>';
		</tal:block>
		<td tal:condition="php: not:is_advanced and filter_cols gt 3" class="small-caption" colspan="' . ( $t_filter_cols - 2 ) . '">&#160;</td>
		</tr>
	</table>

	collapse_icon( 'filter' );
	<div class="search-box">
	<label tal:content="string:${lang/search}&#160;">
	<input type="text" size="16" tal:attributes="name FILTER_PROPERTY_SEARCH;value string_html_specialchars( $t_filter[FILTER_PROPERTY_SEARCH] )" />
	</label>
	</div>
	<div class="submit-query"><input type="submit" name="filter" tal:attributes="value lang/filter_button" /></div>
	</form>

	<div tal:condition="can_create_stored_query" class="save-query">
		<form method="post" name="save_query" action="query_store_page.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="submit" name="save_query_button" class="button-small" value="<?php echo lang_get( 'save_query' )?>" />
		</form>
	</div>
	<tal:block tal:condition="stored_queries_arr">
	<div class="manage-queries">
		<form method="post" name="open_queries" action="query_view_page.php" tal:comment="CSRF protection not required here - form does not result in modifications">
			<input type="submit" name="switch_to_query_button" class="button-small" value="<?php echo lang_get( 'open_queries' )?>" />
		</form>
	</div>
	<div class="stored-queries">
		<form method="get" name="list_queries<?php echo $t_form_name_suffix;?>" action="view_all_set.php" tal:comment="CSRF protection not required here - form does not result in modifications">
			<input type="hidden" name="type" value="3" />
			<select name="source_query_id">
				<option value="-1"><?php echo '[' . lang_get( 'reset_query' ) . ']'?></option>
				<option value="-1"></option>

				$t_source_query_id = isset( $t_filter['_source_query_id'] ) ? $t_filter['_source_query_id'] : -1;
				foreach( $t_stored_queries_arr as $t_query_id => $t_query_name ) {
					echo '<option value="' . $t_query_id . '" ';
					check_selected( $t_query_id, $t_source_query_id );
					echo '>' . string_display_line( $t_query_name ) . '</option>';
				}
	
				</select>
				<input type="submit" name="switch_to_query_button" class="button-small" tal:attributes="value lang/use_query" />
			</form>
		</div>
		</tal:block> 
		<div tal:condition="not: stored_queries_arr" class="reset-query">
			<form method="get" name="reset_query" action="view_all_set.php" tal:comment="CSRF protection not required here - form does not result in modifications">
				<input type="hidden" name="type" value="3" />
				<input type="hidden" name="source_query_id" value="-1" />
				<input type="submit" name="reset_query_button" class="button-small" tal:attributes="value lang/reset_query" />
			</form>
		</div>
		<div class="filter-links">
			<span tal:condition="show_switch_link" class="switch-view"><a tal:attributes="href switch_view_link" tal:content="switch_view_label"></a></span>
			<span tal:condition="show_create_permalink" class="permalink"><a href="permalink_page.php?url=', urlencode( filter_get_url( $t_filter ) ), '" tal:content="lang/create_filter_link">Create Filter</a></span>
		</div>
	</div>
	<br />
</div>
