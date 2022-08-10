<?php

/* Template Name: Standards

Template Post Type: Page

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+ */

get_header();

global $wpdb;
global $post;

$site_url = get_site_url().'/yardstick';
$slug = $post->post_name;
	
$master_loc = $_SESSION['master_loc'];
	
$user_id = get_current_user_id();
$user_role = $_SESSION['user_role'];
$user_role_tag = $_SESSION['user_role_tag'];
$wp_user_role = $_SESSION['wp_user_role'];

$plan_id = $_SESSION['plan_id'];

$edit = $_GET['edit'];

$entry_date = date( 'Y-m-d H:i:s' );

// NEEED TO ERROR CHECK USER HAS ACCESS TO STANDARD
$organisation_url = $_GET['organisation'];
$organisation = str_replace( '_', ' ', $organisation_url );
$standard_url = $_GET['standard'];
$standard = str_replace( '_', ' ', $standard_url );
$standard_setup = $wpdb->get_row( "SELECT master_standard.id, loc_name, standard, description FROM master_standard INNER JOIN relation_standard ON master_standard.id=relation_standard.std_id INNER JOIN profile_location ON master_standard.loc_id=profile_location.parent_id WHERE relation_standard.user_id=$user_id AND loc_name='$organisation' AND standard='$standard' AND master_standard.active=1" );
$std_id = $standard_setup->id;
$std_organisation = $standard_setup->loc_name;
$std_name = $standard_setup->standard;
$std_description = $standard_setup->description; ?>

<article class="col-xl-8 px-3"> 
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		<header class="header">
			<h1 class="h4-style">Standard <i class="fal fa-chevron-double-right small"></i> <?php echo $std_name ?></h1> 
			<p><?php echo $std_description ?></p>
		</header> <?php
	
		$results = $wpdb->get_results( "SELECT std_id, user_id FROM data_standard INNER JOIN master_question ON data_standard.question_id=master_question.id WHERE user_id=$user_id and std_id=$std_id" );
				
		$latest_submission = $wpdb->get_row( "SELECT entry_date, display_name FROM data_standard INNER JOIN yard_users ON data_standard.user_id=yard_users.id INNER JOIN master_question ON data_standard.question_id=master_question.id WHERE std_id=$std_id AND loc_id=$master_loc GROUP BY entry_date ORDER BY entry_date DESC" );
	
		$latest_submission_entry_date = $latest_submission->entry_date;
		$latest_submission_entry_date_formatted = date_format( date_create( $latest_submission_entry_date ), 'd-M-Y H:i' );
		$latest_submission_display_name = $latest_submission->display_name;
	
		if( empty( $results ) || $edit == 'on' ) : ?>
			
			<p  class="mb-5"><small>Fields marked with an asterisk <span class="gfield_required">*</span>  are required</small></p>

			<form method="post" name="update" id="standard_submit"> <?php

				$question_results = $wpdb->get_results( "SELECT id, sequence, question, description, required, tag_id, label_align, note FROM master_question WHERE std_id=$std_id ORDER BY sequence" );

				foreach( $question_results as $question_result ) :

					$question_id = $question_result->id;
					$question_sequence = $question_result->sequence;
					$question = $question_result->question;
					$question_description = $question_result->description;
					$question_required = $question_result->required;
					$question_tag_id = $question_result->tag_id;
					$question_label_align = $question_result->label_align;
					$question_conditional = $question_result->conditional;
					$question_cond_id = $question_result->cond_id;
					$question_comment = $question_result->note;
					$question_input = 'question'.$question_id;
					$question_input_id = 'question_id'.$question_id;
					$question_input_comment = 'comment'.$question_id;

					if( $question_label_align == 'left' && $question_comment == 0 ) :
						$class_align_label = 'col-sm-5';
						$class_align_field = 'col-sm-7';
					elseif( $question_label_align == 'top' && $question_comment == 1 ) :
						$class_align_label = 'col-sm-12';
						$class_align_field = 'col-sm-5';
					else : 
						$class_align_label = 'col-sm-12';
						$class_align_field = 'col-sm-12';
					endif;
				
					$latest_answer = $wpdb->get_row( "SELECT answer_id, custom_answer, note, parent_id FROM data_standard WHERE question_id=$question_id AND user_id=$user_id AND entry_date='$latest_submission_entry_date'" );
					$latest_answer_id = $latest_answer->answer_id;
					$latest_custom_answer = $latest_answer->custom_answer;
					$latest_note = $latest_answer->note;
					$latest_parent_id = $latest_answer->parent_id;

					if( $question_required == 1 ) : $required = 'required'; $required_astrick = '<span class="gfield_required"> *</span>'; else : $required = ''; $required_astrick = ''; endif;

					if( $question_tag_id == 272 ) : // text input ?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<input type="text" class="form-control" id="<?php echo $question_input ?>" name="<?php echo $question_input ?>" value="<?php echo $latest_custom_answer ?>" <?php echo $required ?>>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div> <?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_tag_id == 273 ) : // number input ?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<input type="number" class="form-control" id="<?php echo $question_input ?>" name="<?php echo $question_input ?>" value="<?php echo $latest_custom_answer ?>" <?php echo $required ?>>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div> <?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_tag_id == 274 ) : // yes/no radio ?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>Yes" name="<?php echo $question_input_id ?>" value="2" <?php echo $required; if ( $latest_answer_id == 2 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>Yes">Yes</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>No" name="<?php echo $question_input_id ?>" value="1" <?php echo $required; if( $latest_answer_id == 1 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>No">No</label>
								</div>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div> <?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_tag_id == 275 ) : // yes/no/na radion?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label> 
							<div class="<?php echo $class_align_field ?>">
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>Yes" name="<?php echo $question_input_id ?>" value="2" <?php echo $required; if ( $latest_answer_id == 2 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>Yes">Yes</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>No" name="<?php echo $question_input_id ?>" value="1" <?php echo $required; if ( $latest_answer_id == 1 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>No">No</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>NA" name="<?php echo $question_input_id ?>" value="3" <?php echo $required; if ( $latest_answer_id == 3 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>NA">N/A</label>
								</div>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div><?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_tag_id == 285 ) : // yes/no/dont know radio ?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>Yes" name="<?php echo $question_input_id ?>" value="2" <?php echo $required; if ( $latest_answer_id == 2 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>Yes">Yes</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>No" name="<?php echo $question_input_id ?>" value="1" <?php echo $required; if ( $latest_answer_id == 1 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>No">No</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>DontKnow" name="<?php echo $question_input_id ?>" value="10" <?php echo $required; if ( $latest_answer_id == 10 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>DontKnow">Don't Know</label>
								</div>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div> <?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_tag_id == 286 ) : // yes/no/na/dont know radio ?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label> 
							<div class="<?php echo $class_align_field ?>">
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>Yes" name="<?php echo $question_input_id ?>" value="2" <?php echo $required; if ( $latest_answer_id == 2 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>Yes">Yes</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>No" name="<?php echo $question_input_id ?>" value="1" <?php echo $required; if ( $latest_answer_id == 1 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>No">No</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>NA" name="<?php echo $question_input_id ?>" value="3" <?php echo $required; if ( $latest_answer_id == 3 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>NA">N/A</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" id="<?php echo $question_input ?>DontKnow" name="<?php echo $question_input_id ?>" value="10" <?php echo $required; if ( $latest_answer_id == 10 ) : echo ' checked'; endif; ?>>
									<label class="form-check-label" for="<?php echo $question_input ?>DontKnow">Don't Know</label>
								</div>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div><?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_tag_id == 284 ) : // select ?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<select class="form-control" id="<?php echo $question_input ?>" name="<?php echo $question_input_id ?>">

									<option value="" selected disabled>Select <?php echo $question ?></option> <?php

									$answer_results = $wpdb->get_results( "SELECT id, answer FROM master_answer WHERE question_id=$question_id ORDER BY answer ASC" );

									foreach( $answer_results as $answer_result ) :

										$answer_id = $answer_result->id;
										$answer = $answer_result->answer; ?>

										<option value="<?php echo $answer_id ?>"><?php echo $answer ?></option> <?php

									endforeach; ?>

								</select>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div><?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_tag_id == 291 ) : // checkbox ?>

						<div class="form-group row">
							<label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>"> <?php

								$answer_results = $wpdb->get_results( "SELECT id, answer FROM master_answer WHERE question_id=$question_id ORDER BY answer ASC" );

								foreach( $answer_results as $answer_result ) :

									$answer_id = $answer_result->id;
									$answer = $answer_result->answer; ?>

									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="<?php echo $question_input_id ?>" id="inlineCheckbox<?php echo $answer_id ?>" value="<?php echo $answer_id ?>">
										<label class="form-check-label" for="inlineCheckbox<?php echo $answer_id ?>"><?php echo $answer ?></label>
									</div> <?php

								endforeach; ?>

							</div>
							<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>"><?php

						if( $question_comment == 0 ) : ?> </div> <?php endif;

					endif;

					if( $question_comment == 1 ) : ?>

							<div class="col-sm-7">
								<textarea class="form-control" id="<?php echo $question_input_comment ?>" name="<?php echo $question_input_comment ?>"  value="<?php echo $latest_note ?>" placeholder="Comments" rows="1"></textarea>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div>
						</div> <?php

					endif;

					if( $question_tag_id == 276 ) : // section title

						if( $question_sequence != 1 ) : $border_top_class = 'border-top pt-3'; endif; ?>

						<h5 class="<?php echo $border_top_class ?>"><?php echo $question ?></h5>
						<small class="form-text"><?php echo $question_description ?></small> <?php

					endif;

					if( $question_tag_id == 277 ) : // small textbox ?>

							<div class="form-group row"><label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<textarea class="form-control" id="<?php echo $question_input ?>" name="<?php echo $question_input ?>" value="<?php echo $latest_custom_answer ?>" rows="1" <?php echo $required ?>></textarea>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div>
						</div> <?php

					endif;

					if( $question_tag_id == 278 ) : // medium textbox ?>

						<div class="form-group row"><label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<textarea class="form-control" id="<?php echo $question_input ?>" name="<?php echo $question_input ?>" value="<?php echo $latest_custom_answer ?>" rows="2" <?php echo $required ?>></textarea>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div>
						</div> <?php

					endif;

					if( $question_tag_id == 279 ) : // large textbox ?>

						<div class="form-group row"><label for="<?php echo $question_input ?>" class="<?php echo $class_align_label ?> d-block"><?php echo $question.$required_astrick ?> <small><?php echo $question_description ?></small></label>
							<div class="<?php echo $class_align_field ?>">
								<textarea class="form-control" id="<?php echo $question_input ?>" name="<?php echo $question_input ?>" value="<?php echo $latest_custom_answer ?>" rows="3" <?php echo $required ?>></textarea>
								<input type="hidden" id="latestParentID" name="latest_parent_id" value="<?php echo $latest_parent_id; ?>">
							</div>
						</div> <?php

					endif;


				endforeach; ?>

				<button type="submit" name="standard_submit" class="btn btn-primary" onclick="return redirect();" >Submit</button>

			</form> <?php

			foreach( $question_results as $question_result ) :
				$question_input_id = $question_result->id;
				$question_input_submit = 'question'.$question_result->id;
				$question_input_id_submit = 'question_id'.$question_result->id;
				$question_input_comment_submit = 'comment'.$question_result->id;

				$question_submit_null = $_POST[$question_input_submit];
				$question_id_submit_null = $_POST[$question_input_id_submit];
				$question_comment_submit_null = $_POST[$question_input_comment_submit];
				$latest_parent_id_submit = $_POST['latest_parent_id'];

				if( empty( $question_submit_null ) ) : $question_submit = NULL; else : $question_submit = $question_submit_null; endif;

				if( $question_id_submit_null == 'NA' ) : $question_id_submit = NULL; else : $question_id_submit = $question_id_submit_null; endif;

				if( empty( $question_comment_submit_null ) ) : $question_comment_submit = NULL; else : $question_comment_submit = $question_comment_submit_null; endif;

				if ( isset( $_POST['standard_submit'] ) && $edit == 'on' ) :

					$wpdb->insert( 'data_standard',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry_revision',
							'question_id' => (int)$question_input_id,
							'custom_answer' => $question_submit,
							'answer_id' => (int)$question_id_submit,
							'note' => $question_comment_submit,
							'parent_id' => $latest_parent_id_submit,
							'user_id' => (int)$user_id,
							'loc_id' => (int)$master_loc
						)
					);

					echo "<meta http-equiv='refresh' content='0'>";
	
				elseif ( isset( $_POST['standard_submit'] ) ) :

					$wpdb->insert( 'data_standard',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'question_id' => (int)$question_input_id,
							'custom_answer' => $question_submit,
							'answer_id' => (int)$question_id_submit,
							'note' => $question_comment_submit,
							'parent_id' => 0,
							'user_id' => (int)$user_id,
							'loc_id' => (int)$master_loc
						)
					);
	
					$parent_id = $wpdb->insert_id;

					$wpdb->update( 'data_standard',
						array(
							'parent_id' => $parent_id,
						),
						array(
							'id' => $parent_id
						)
					);

					echo "<meta http-equiv='refresh' content='0'>";

				endif;

			endforeach;
		
		else : ?>
	
			<hr /> <?php
	
			$submissions= $wpdb->get_results( "SELECT data_standard.id, question, custom_answer, answer, data_standard.note FROM data_standard INNER JOIN master_question ON data_standard.question_id=master_question.id LEFT JOIN master_answer ON data_standard.answer_id=master_answer.id WHERE std_id=$std_id AND loc_id=$master_loc AND entry_date='$latest_submission_entry_date' ORDER by data_standard.id ASC" );

			foreach( $submissions as $submission ) :
									
				$submission_question = $submission->question;
				$submission_custom_answer = $submission->custom_answer;
				$submission_system_answer = $submission->answer;
				$submission_note= $submission->note;
										
				if( empty( $submission_custom_answer ) ) : $submission_answer = $submission_system_answer; else : $submission_answer = $submission_custom_answer; endif;
										
				if( !empty( $submission_note ) ) : $submission_comment = '<br />Comments: '.$submission_note; else : $submission_comment = ''; endif;
										
				echo '<p><b>'.$submission_question.'</b>: '.$submission_answer.$submission_comment.'</p>';

			endforeach;
												
			echo '<p>Entered on '.$latest_submission_entry_date_formatted.' by '.$latest_submission_display_name.'</p>'; ?>
	
			<a href="<?php echo $site_url.'/'.$slug.'/?organisation='.$organisation_url.'&standard='.$standard_url.'&edit=on' ?>" class="btn btn-primary" title="Edit">Edit</a> <?php
	
		endif; ?>
		
	</section>
</article>
	
<aside class="col-xl-4 pr-3"> <?php
		
	$standard_results = $wpdb->get_results( "SELECT loc_name, standard FROM master_standard INNER JOIN relation_standard ON master_standard.id=relation_standard.std_id INNER JOIN profile_location ON master_standard.loc_id=profile_location.parent_id WHERE profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id) AND relation_standard.user_id=$user_id ORDER BY master_standard.id ASC" ); ?>
		
	<section class="secondary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		<h2 class="h4-style"><?php echo $organisation ?></h2> 
		
		<ul class="chevron-circle"> <?php
			
			foreach( $standard_results as $standard_result ) :

				$organisation_link = $standard_result->loc_name;
				$standard_link = $standard_result->standard;
				$organisation_url = strtolower( str_replace( ' ', '_', $organisation_link ) );
				$standard_url = strtolower( str_replace( ' ', '_', $standard_link ) );

				echo '<li><a href="'.$site_url.'/'.$slug.'/?organisation='.$organisation_url.'&standard='.$standard_url.'" title="'.$standard_link.'">'.$standard_link.'</a></li>';

			endforeach; ?>
				
		</ul>
	</section>
		
	<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		<h2 class="h4-style">Revisions</h2> <?php
		
		if( empty( $results ) ) :
			
			echo '<p>No '.strtolower( $standard ).' standards have been submitted.</p>';
		
		endif;
			
		$revision_submissions= $wpdb->get_results( "SELECT entry_date, display_name FROM data_standard INNER JOIN yard_users ON data_standard.user_id=yard_users.id INNER JOIN master_question ON data_standard.question_id=master_question.id WHERE std_id=$std_id AND loc_id=$master_loc GROUP BY entry_date ORDER BY entry_date DESC" ); ?>
			
		<table id="revisions" class="table table-borderless"> 
			<tbody> <?php
					
					$i = 1;
			
					foreach( $revision_submissions as $revision_submission ) :
					
						$revision_submission_id = $i++;
						$revision_submission_entry_date_unformated = $revision_submission->entry_date;
						$revision_submission_entry_date = date_create( $revision_submission->entry_date );
						$revision_submission_display_name = $revision_submission->display_name; ?>

						<tr>
							<td> 
								<button type="button" class="btn btn-dark" data-toggle="modal" data-target="#modal-<?php echo $revision_submission_id ?>"><i class="far fa-eye"></i></button>

								<div class="modal fade" id="modal-<?php echo $revision_submission_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $revision_submission_id ?>" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
										<div class="modal-content">

											<div class="modal-header">
												<h5 class="modal-title" id="viewSubmissionTitle"><?php echo $std_name ?></h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
											</div>

											<div class="modal-body"> <?php echo '<p>Entered on '.date_format( $revision_submission_entry_date, "d-M-Y H:i" ).' by '.$revision_submission_display_name.'</p>';
	
												$submissions= $wpdb->get_results( "SELECT data_standard.id, question, custom_answer, answer, data_standard.note FROM data_standard INNER JOIN master_question ON data_standard.question_id=master_question.id LEFT JOIN master_answer ON data_standard.answer_id=master_answer.id WHERE std_id=$std_id AND loc_id=$master_loc AND entry_date='$revision_submission_entry_date_unformated' ORDER by data_standard.id ASC" );
										
												foreach( $submissions as $submission ) :
									
													$submission_question = $submission->question;
													$submission_custom_answer = $submission->custom_answer;
													$submission_system_answer = $submission->answer;
													$submission_note= $submission->note;
										
													if( empty( $submission_custom_answer ) ) : $submission_answer = $submission_system_answer; else : $submission_answer = $submission_custom_answer; endif;
										
													if( !empty( $submission_note ) ) : $submission_comment = '<br />Comments: '.$submission_note; else : $submission_comment = ''; endif;
										
													echo '<p><b>'.$submission_question.'</b>: '.$submission_answer.$submission_comment.'</p>';

												endforeach; ?>
									
											</div>

										</div>
									</div>
								</div>
							</td>
							
							<td><?php echo '<p>'.date_format( $revision_submission_entry_date, "d-M-Y H:i" ).'<br />By '.$revision_submission_display_name.'</p>'; ?></td>
						</tr> <?php

					endforeach; ?>
				</tbody>
			</table>
			
		</section>
						
</aside>
		
<!-- JQuery Datatables -->
<script>
	$(document).ready(function() {
    	$('#revisions').DataTable({
			searching: false,
			paging: false,
			info: false,
			columnDefs: [{ orderable: false }],
			bSort: false
		});
	});
</script> <?php

get_footer(); ?>