<?php
class sfWidgetFormSchemaFormatterCustom extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "\n%label%\n%error%%field%%help%%hidden_fields%\n",
    $decoratorFormat = "\n%content%\n";
}
