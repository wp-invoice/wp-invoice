<?php

/**
  Template Name: Modern
 */

$logo = '<td rowspan="2" width="150"><img src="%logo%" width="150" /></td>';
$header_width = 'width="442"';

$due_date = '<tr><td align="right" width="50%"><strong>' . __('DUE DATE', WPI) . ':</strong></td><td align="left" width="50%">%due_date%</td></tr>';
$amount_due = '<tr><td align="right" width="50%"><strong>' . __('AMOUNT DUE', WPI) . ':</strong></td><td align="left" width="50%"><span style="color: #198153">%amount_due%</span></td></tr>';
$attn = '<tr><td align="right" width="50%"><strong>' . __('ATTN', WPI) . ':</strong></td><td align="left" width="50%">%attn%</td></tr>';
$bill_to = '<tr><td align="right" width="50%"><strong>' . __('BILL TO', WPI) . ':</strong></td><td align="left" width="50%"><span style="color: #3e8eaf;">%bill_to%</span></td></tr>';
$address = '<tr><td align="right" width="50%"><strong>' . __('ADDRESS', WPI) . ':</strong></td><td align="left" width="50%">%address%</td></tr>';
$telephone = '<tr><td align="right" width="50%"><strong>' . __('TELEPHONE', WPI) . ':</strong></td><td align="left" width="50%">%telephone%</td></tr>';

//** Display Subtotal */
$subtotal = '<strong>' . __('TOTAL', WPI) . ': <span style="color: #198556">%subtotal%</span></strong><br>';

//** Display Tax value if it is greater then 0 */
$total_tax = '<strong>' . __('TAX', WPI) . ': <span style="color: #198556">%total_tax%</span></strong><br>';

//** Display Discount value if it is greater then 0 */
$total_discount = '<strong>' . __('DISCOUNT', WPI) . ': <span style="color: #198556">%total_discount%</span></strong><br>';

//** Display Balance value if it is greater then 0 */
$grand_total = '<strong>' . __('BALANCE', WPI) . ': <span style="color: #bc4873">%grand_total%</span></strong><br>';

$tax_th = '<td width="100">' . __('TAX', WPI) . '</td>';
$tax_td = '<td bgcolor="#f0f0f0"><b><span style="color: #1b8d5b">%line_total_tax%</span></b></td>';

$name_and_address = '
  <td width="49%" style="background-color: #f0f0f0;">
    <table border="0" cellspacing="5" cellpadding="0" align="center" width="100%">
      %bill_to%
      %address%
      %telephone%
    </table>
  </td>
';

$description_table = '
  <table border="0" cellspacing="0" cellpadding="3">
    <tr>
      <td width="%desc_width%">' . __('DESCRIPTION', WPI) . '</td>
      <td width="100">' . __('QUANTITY', WPI) . '</td>
      <td width="100">' . __('AMOUNT', WPI) . '</td>
      %tax_th%
    </tr>
    %description_row%
  </table><table border="0" cellspacing="0" cellpadding="3">
    <tr>
      <td style="border-top: 1px solid #cdcdcd;border-bottom: 1px solid #cdcdcd;" align="right">%subtotal%%total_tax%%total_discount%%grand_total%
      </td>
    </tr>
  </table>
';
$description_row = '
  <tr>
    <td bgcolor="#f0f0f0"><b>%name%</b></td>
    <td bgcolor="#f0f0f0"><b>%quantity%</b></td>
    <td bgcolor="#f0f0f0"><b><span style="color: #1b8d5b">%price%</span></b></td>
    %tax_td%
  </tr>
  <tr>
    <td colspan="%description_cols%"><i>%description%</i></td>
  </tr>
';

$terms_n_conditions = '
  <tr>
    <td>
      <table border="0" cellspacing="5" cellpadding="5" width="100%">
        <tr>
          <td style="color: #474747;">' . __('TERMS &amp; CONDITIONS', WPI) . '</td>
        </tr>
        <tr>
          <td style="color: #474747; font-size: 0.8em;">%terms_n_conditions_text%</td>
        </tr>
      </table>
    </td>
  </tr>
';

$notes = '
  <tr>
    <td>
      <table border="0" cellspacing="5" cellpadding="5" width="100%">
        <tr>
          <td style="color: #474747;">' . __('NOTES', WPI) . '</td>
        </tr>
        <tr>
          <td style="color: #474747; font-size: 0.8em;">%notes_text%</td>
        </tr>
      </table>
    </td>
  </tr>
';

$content = '
  <tr>
    <td style="border-bottom:1px solid #cdcdcd;">
      <table border="0" cellspacing="5" cellpadding="5" width="100%">
        <tr>
          <td style="color: #474747; font-size: 0.8em;">%content_text%</td>
        </tr>
      </table>
    </td>
  </tr>  
';

$html = '
  <table border="0" cellspacing="0" cellpadding="10" width="100%">
    <tr>
      <td>
        <table border="0" cellspacing="0" cellpadding="3" width="100%">
          <tr>
            %logo%
            <td style="border-bottom: 1px solid #cdcdcd; font-size: 30px;" align="center" %header_width%><span style="color: #7a7a7a">%business_address%</span> %business_phone%<br />%email_address% <span style="color: #7a7a7a">*</span> %url%</td>
          </tr>
          <tr>
            <td align="center"><strong>%is_quote%</strong> <span style="color: #3e8eaf">#%id%</span> * <i>%post_date%</i></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="border-top: 1px solid #cdcdcd; border-bottom: 1px solid #cdcdcd;">
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="49%" style="background-color:#f0f0f0;">
              <table border="0" cellspacing="5" cellpadding="0" width="100%">
                %due_date%
                %amount_due%
                %attn%
              </table>
            </td>
            <td width="2%">&nbsp;</td>
            %name_and_address%
          </tr>
        </table>
      </td>
    </tr>
    %content%
    <tr><td>%description%</td></tr>
    %terms_n_conditions%
    %notes%
  </table>
';
?>