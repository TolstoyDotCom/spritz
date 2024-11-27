<?php echo wp_kses( $nonceField, [ 'input' => [ 'id' => [], 'type' => [], 'name' => [], 'value' => [] ] ] ); ?>
<p>
	<?php if ( $message ) { ?>
		<div class="spritz_message"><?php echo esc_html( $message ); ?></div>
	<?php } ?>


	<?php if ( !empty( $current_state_explanation ) ) { ?>
		<div class="spritz_current_state_explanation"><?php echo esc_html( $current_state_explanation ); ?></div>
		<div class="spritz_current_state_public_note"><?php echo esc_html( $current_state_public_note ); ?></div>
		<div class="spritz_current_state_next_action_date"><?php echo esc_html( $current_state_next_action_date ); ?></div>
	<?php } ?>


	<?php if ( !empty( $transition_options ) ) { ?>
		<br />
		<label for="spritz_transition"><?php echo esc_html( $transition_label ); ?></label>
		<br />
		<select name="spritz_transition" id="spritz_transition">
		<?php foreach ( $transition_options as $option => $label ) { ?>
			<option value="<?php echo esc_attr( $option ); ?>" ><?php echo esc_html( $label ); ?></option>
		<?php } ?>
		</select>

		<br /><br />
		<label for="spritz_public_note"><?php echo esc_html( $public_note_label ); ?></label>
		<br />
		<textarea class="widefat" name="spritz_public_note" id="spritz_public_note" rows="3" cols="30"><?php echo esc_textarea( $public_note_value ); ?></textarea>

		<br /><br />
		<label for="spritz_private_note"><?php echo esc_html( $private_note_label ); ?></label>
		<br />
		<textarea class="widefat" name="spritz_private_note" id="spritz_private_note" rows="3" cols="30"><?php echo esc_textarea( $private_note_value ); ?></textarea>

		<br /><br />
		<label>
			<input class="checkbox" type="checkbox" name="spritz_confirm_transition" id="spritz_confirm_transition" />
			<?php echo esc_html( $confirm_transition ); ?>
		</label>
	<?php } ?>


	<?php if ( !empty( $show_admin_panel ) ) { ?>
		<hr/>

		<div class="spritz_admin_panel_title"><?php echo esc_html( $admin_panel_title ); ?></div>

		<br />
		<label>
			<input class="checkbox" type="checkbox" name="spritz_auto_approve" />
			<?php echo esc_html( $auto_approve_text ); ?>
		</label>

		<br /><br />
		<label for="spritz_num_days"><?php echo esc_html( $num_days_label ); ?></label>
		<br />
		<input type="number" step="1" class="widefat" name="spritz_num_days" id="spritz_num_days" value="<?php echo esc_attr( $spritz_num_days ); ?>"/>

		<br /><br />
		<label>
			<input class="checkbox" type="checkbox" name="spritz_confirm_override" id="spritz_confirm_override" />
			<?php echo esc_html( $confirm_override ); ?>
		</label>
	<?php } ?>


</p>
