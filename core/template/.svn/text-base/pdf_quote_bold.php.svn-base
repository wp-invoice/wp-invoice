<?php

/**
  Template Name: Bold
 */

$font = 'impact';
$logo = '<td width="150"><img src="%logo%" width="150" /></td>';
$header_width = 'width="450"';

$due_date = 'DUE: %due_date%';
$amount_due = '<strong>' . __('AMOUNT DUE', WPI) . ':</strong> <span style="color: #bc4873">%amount_due%</span>';
$attn = '<strong>' . __('ATTN', WPI) . ':</strong> %attn%';

$bill_to = '<span style="color: #22b273"><b>%bill_to%</b></span>';
$address = '<span style="color: #7a7a7a"><b>%address%</b></span>';
$telephone = '<strong>%telephone%</strong>';
$name_and_address = '<td width="50%" align="center">%bill_to%<br />%address%<br />%telephone%<br /></td>';

//** Display Subtotal */
$subtotal  = '<strong>' . __('TOTAL', WPI) . ': <span style="color: #bc4873">%subtotal%</span></strong><br>';

//** Display Tax value if it is greater then 0 */
$total_tax = '<strong>' . __('TAX', WPI) . ': <span style="color: #bc4873">%total_tax%</span></strong><br>';

//** Display Discount value if it is greater then 0 */
$total_discount = '<strong>' . __('DISCOUNT', WPI) . ': <span style="color: #bc4873">%total_discount%</span></strong><br>';

//** Display Balance value if it is greater then 0 */
$grand_total = '<strong>' . __('BALANCE', WPI) . ': <span style="color: #bc4873">%grand_total%</span></strong><br>';

$tax_th = '<td style="color: #d4d4d4;" width="90"><strong>' . __('TAX', WPI) . '</strong></td>';
$tax_td = '<td bgcolor="#d4d4d4" style="color: #3e8eaf; text-transform: uppercase"><b>%line_total_tax%</b></td>';

$description_table = '<table border="0" cellspacing="0" cellpadding="3">
    <tr>
      <td style="color: #d4d4d4;" width="%desc_width%"><strong>' . __('DESCRIPTION', WPI) . '</strong></td>
      <td style="color: #d4d4d4;" width="90"><strong>' . __('QTY', WPI) . '</strong></td>
      <td style="color: #d4d4d4;" width="90"><strong>' . __('SUM', WPI) . '</strong></td>
      %tax_th%
    </tr>
    %description_row%
    </table><table border="0" cellspacing="0" cellpadding="3">
    <tr>
      <td style="border-top: 1px solid #cdcdcd;" align="right">%subtotal%%total_tax%%total_discount%%grand_total%
      </td>
    </tr>
  </table>
';

$description_row = '
  <tr>
    <td bgcolor="#d4d4d4" style="text-transform: uppercase"><b>%name%</b></td>
    <td bgcolor="#d4d4d4" style="text-transform: uppercase"><b>%quantity%</b></td>
    <td bgcolor="#d4d4d4" style="color: #3e8eaf; text-transform: uppercase"><b>%price%</b></td>
    %tax_td%
  </tr>
  <tr>
    <td><small>%description%</small></td>
  </tr>
';

$terms_n_conditions = '
  <tr>
    <td><table border="0" cellspacing="5" cellpadding="5" width="100%">
        <tr><td style="border-bottom: 1px solid #474747;color: #474747;">' . __('TERMS &amp; CONDITIONS', WPI) . '</td>
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
    <td><table border="0" cellspacing="5" cellpadding="5" width="100%">
        <tr>
          <td style="border-bottom: 1px solid #474747;color: #474747;">' . __('NOTES', WPI) . '</td>
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
    <td style="border-top: 1px solid #cdcdcd;;color: #474747;"><table border="0" cellspacing="5" cellpadding="5" width="100%">
        <tr>
          <td style="color: #474747; font-size: 0.8em;">%content_text%</td>
        </tr>
      </table>
    </td>
  </tr>  
';

$html = '<table border="0" cellspacing="5" cellpadding="5" width="600">
    <tr>
      <td style="border-bottom: 1px solid #cdcdcd;"><table border="0" cellspacing="3" cellpadding="3" width="100%">
          <tr>
            %logo%
            <td align="center" %header_width%><small><strong><span style="color: #7a7a7a">%business_address%</span> %business_phone%<br />%email_address% * %url%</strong></small></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td><table border="0" cellspacing="5" cellpadding="5" width="100%">
          <tr><td align="center"><strong><span style="">%is_quote% <span style="color: #50e6b1">#</span><span style="color: #3e8eaf">%id%</span></span><br /><span style="color: #b8b8b8">%post_date%</span> <span style="color: #7a7a7a;">%due_date%</span><br />%amount_due%</strong></td>%name_and_address%
          </tr>
        </table>
      </td>
    </tr>
    %content%
    <tr><td style="border-top: 1px solid #cdcdcd;">%description%</td></tr>
    %terms_n_conditions%
    %notes%
  </table>
';
?>