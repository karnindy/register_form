<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

$missing_vars = "
\$courseType = p('courseType');
\$agentLevel = p('agentLevel');
\$brokerLevel = p('brokerLevel');
\$licenseStatus = p('licenseStatus');
\$renewAgent1 = p('renewAgent1');
\$renewAgent2 = p('renewAgent2');
\$renewAgent3 = p('renewAgent3');
\$renewBroker1 = p('renewBroker1');
\$renewBroker2 = p('renewBroker2');
\$renewBroker3 = p('renewBroker3');
\$renewOther = p('renewOther');
\$trainingExemption = p('trainingExemption');

// Default empty strings to null for database
\$agentLevel = \$agentLevel === '' ? null : \$agentLevel;
\$brokerLevel = \$brokerLevel === '' ? null : \$brokerLevel;
\$licenseStatus = \$licenseStatus === '' ? null : \$licenseStatus;
";

$c = preg_replace('/\$agentType = p\(\'agentType\'\);/s', $missing_vars . "\n\$agentType = p('agentType');", $c);

file_put_contents($f, $c);
echo "Injected missing variables\n";
