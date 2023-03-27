<?php

echo "<input type='text' id='search-input' size='80%' placeholder=".search.">";
echo "<table id='search-table' cellpadding='20' cellspacing='10'>";
echo "<thead>";
echo "  <tr>";
echo "      <th>".name."</th>";
echo "      <th>".surname."</th>";
echo "      <th>".login."</th>";
echo "      <th>".email."</th>\n";
//echo "      <th>".member_of."</th>\n";
echo "      <th align=\"right\">".actions."</th>\n";
echo "  </tr>";
echo "</thead>";
echo "<tbody id='table-body'></tbody>";
echo "</table>";
?>
 

<script src="../scripts/home-list.js"></script>





