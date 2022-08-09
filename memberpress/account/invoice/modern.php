<?php
/**
 * The template for displaying PDF Invoice
 * Override by copying it to yourtheme/memberpress/account/invoice/modern.php.
 */

$color = isset($invoice->color) && !empty($invoice->color) ? $invoice->color : '#3993d1';
?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style lang="text/css">
		@page {
			margin: 0;
		}
		
		body {
			background: #fff;
			color: #000000;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			font-size: 14px;
			font-weight: 400;
			margin: 0 auto;
			position: relative;
		}
		
		header {
			border-bottom: 1px solid #e1e1e1;
			padding: 2em 2em 4em;
		}
		
		table {
			border-collapse: collapse;
			border-spacing: 0;
			width: 100%;
		}
		
		table td {
			vertical-align: top;
		}
		
		table tfoot {
			border-top: 1px solid #e1e1e1;
		}
		
		table#billing-info {
			margin: 4em 2em 2em;
		}
		
		table#notes {
			margin: 6em 2em 2em;
		}
		
		table#billing_costs th {
			background: <?php echo $color ?>;
			font-weight: bold;
			padding: 1em 2em;
			text-align: left;
		}
		
		table#billing_costs td {
			padding: 1em 2em;
		}
		
		footer{
			border-top: 1px solid #e1e1e1;
			font-size: 11px;
			position: absolute;
			bottom: 0;
			left: 0;
			right :0;
			padding: 2em 3rem;
			text-align: center;
		}
		
		h1 {
			font-size: 48px;
			font-weight: lighter;
			margin-bottom: 2em;
		}
		
		h2 {
			font-size: 14px;
			font-weight: bold;
			line-height: 1.2;
			margin: 0 0 15px;
		}
	
		
		a {
			color: <?php echo $color ?>;
			text-decoration: underline
		}
		
		.text-right {
			text-align: right;
		}

	</style>
</head>

<body>

	<header class="clearfix">
		<table>
			<tr>
				<td> <?php 
					if( is_numeric( $invoice->logo ) ) : ?>
						<img src="<?php echo get_attached_file( $invoice->logo ); ?>" width="250"> <?php
					endif; ?>
				</td>
				<td class="text-right">
					<h1>INVOICE</h1>
					<p><?php echo wpautop( $invoice->company ); ?></p>
				</td>
			  </tr>
		</table>
	</header>

	<main>
		<table id="billing-info">
			<tr>
				<td>
					<p><strong><?php esc_html_e( 'Bill To:', 'memberpress-pdf-invoice' ) ?></strong></p>
					<p><?php echo wpautop( $invoice->bill_to ); ?></p>
				</td>
				<td>
					<table>
						<tr> <?php
							printf( '<td class="text-right"><p><strong>%s:&nbsp;</strong></p></td> <td><p>%s</p></td>', esc_html__( 'Invoice Number', 'memberpress-pdf-invoice' ), strtoupper( $invoice->invoice_number ) ); ?>
						</tr>

						<tr>
							<td class="text-right"><p><strong>Payment Due:&nbsp;</strong></p></td> <td><p><?php echo date_i18n( 'F d, Y', $invoice->paid_at ) ?></p></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table id="billing_costs">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Description', 'memberpress-pdf-invoice' ); ?></th> <?php
						if( $invoice->show_quantity ) : '<th class="quantity">Quantity</th>'; endif; ?>

					<th><?php esc_html_e( 'Amount', 'memberpress-pdf-invoice' ); ?></th>
				</tr>
			</thead>

			<tbody> <?php
				foreach( $invoice->items as $item ) : ?>
					<tr>
						<td><?php echo $item['description']; ?></td> <?php

						if( $invoice->show_quantity ) :
							'<td>' . $item['quantity'] . '</td>';
						endif; ?>

						<td><?php echo MeprAppHelper::format_currency( $item['amount'] ); ?></td>
					</tr><?php
				endforeach; 

				if( isset( $invoice->coupon ) && ! empty( $invoice->coupon ) && $invoice->coupon['id'] != 0 ) : ?>

					<tr>
						<td><?php echo $invoice->coupon['desc']; ?></td> <?php

						if( $invoice->show_quantity ) : ?>
							<td>&nbsp;</td> <?php
						endif; ?>

						<td>-<?php echo MeprAppHelper::format_currency( $invoice->coupon['amount'], true, false ); ?></td>
					</tr> <?php

				endif;

				if( $invoice->tax['amount'] > 0.00 ) : ?>

					<tr>
						<td><?php esc_html_e( 'Subtotal', 'memberpress-pdf-invoice' ); ?></td> <?php

							if ( $invoice->show_quantity ) : ?>
								<td>&nbsp;</td> <?php
							endif; ?>

						<td><?php echo MeprAppHelper::format_currency( $invoice->subtotal, true, false ); ?></td>
					</tr>

					<tr>
						<td><?php echo MeprUtils::format_tax_percent_for_display( $invoice->tax['percent'] ) . '% ' . $invoice->tax['type']; ?></td> <?php 

							if ( $invoice->show_quantity ) : ?>
								<td>&nbsp;</td> <?php 
							endif; ?>

						<td><?php echo MeprAppHelper::format_currency( $invoice->tax['amount'], true, false ); ?></td>
					</tr> <?php

				endif; ?>

			</tbody>

			<tfoot>

				<tr>
				  <td class="text-right"><strong><?php esc_html_e( 'Total (GBP):', 'memberpress-pdf-invoice' ); ?></strong></td>
				  <td><stong><?php echo MeprAppHelper::format_currency( $invoice->total, true, false ); ?></stong></td>
				</tr>

			</tfoot>
		</table>

		<table id="notes">
			<tr>
				<td>
					<div><?php echo wpautop( $invoice->notes ); ?></div>
				</td>

				<td class="text-right">
					<img style="width: 150px;" src="<?php echo MPDFINVOICE_PATH . 'app/views/account/invoice/paid.jpg'; ?>" alt="">
				</td>
			</tr>
		</table>
	</main>

	<footer>
		<?php echo wpautop( $invoice->footnotes ); ?>
	</footer>

</body>
</html>
