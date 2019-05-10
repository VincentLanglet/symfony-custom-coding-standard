# Coding Standard Rules
## From PSR2

We imported the [PSR2 Standard](./standards/psr2.md) with these overrides:

- There is not line length limit

```
<exclude ref="Generic.Files.LineLength">
```

- There MUST NOT be trailing whitespace at the end of blank lines

```
<rule ref="Squiz.WhiteSpace.SuperfluousWhitespace">
    <properties>
        <property name="ignoreBlankLines" value="false"/>
    </properties>
</rule>
```

- There MUST be one space after type hinting

```
<rule ref="Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterHint">
    <severity>5</severity>
</rule>
```

- PhpDoc comments MUSE use an indent of 4 spaces, and MUST NOT use tabs for indenting.
```
<rule ref="Generic.WhiteSpace.ScopeIndent">
    <properties>
        <property name="ignoreIndentationTokens" type="array">
            <element value="T_COMMENT"/>
        </property>
    </properties>
</rule>
```

## From symfony

We mainly respect the [Symfony Standard](./standards/symfony.md) but
we do not respect this rule:

  - Declare all the arguments on the same line as the method/function name, no matter how many arguments there are

## Others
### Imported
- Do not use `<?` to define a PHP file

```
<rule ref="Generic.PHP.DisallowShortOpenTag"/>
```

- Add a single space after type casting

```
<rule ref="Generic.Formatting.SpaceAfterCast"/>
```

- Do not use space inside type casting

```
<rule ref="Squiz.WhiteSpace.CastSpacing"/>
```

- Use lowercase for PHP functions

```
<rule ref="Squiz.PHP.LowercasePHPFunctions"/>
```

- Variable have scope modifier

```
<rule ref="Squiz.Scope.MemberVarScope"/>
```

- No perl-style comments are used

```
<rule ref="PEAR.Commenting.InlineComment"/>
```

- Use single quotes instead of double quotes

```
<rule ref="Squiz.Strings.DoubleQuoteUsage">
    <exclude name="Squiz.Strings.DoubleQuoteUsage.ContainsVar"/>
</rule>
```

- Do not skip blank line after function opening brace

```
<rule ref="Squiz.WhiteSpace.FunctionOpeningBraceSpace"/>
```

- Do not use space before semicolon

```
<rule ref="Squiz.WhiteSpace.SemicolonSpacing"/>
```

- Do not use `error_log`, `print_r`, `var_dump`, `sizeof`, `delete`, `print`, `is_null` and `create_function`

```
<rule ref="Squiz.PHP.DiscouragedFunctions"/>
<rule ref="Squiz.PHP.ForbiddenFunctions"/>
```

- Use short array syntax

```
<rule ref="Generic.Arrays.DisallowLongArraySyntax"/>
```

- Do not use empty PHP statement

```
<rule ref="Generic.CodeAnalysis.EmptyPHPStatement"/>
```

- Add a single space around logical operator (`&&`, `||`, `...`)

```
<rule ref="Squiz.WhiteSpace.LogicalOperatorSpacing"/>
```

- Do not use space around object operators (`->`)

```
<rule ref="Squiz.WhiteSpace.ObjectOperatorSpacing">
    <properties>
        <property name="ignoreNewlines" value="true"/>
    </properties>
</rule>
```

- DocComment should be correctly indented

```
<rule ref="Squiz.Commenting.DocCommentAlignment">
    <exclude name="Squiz.Commenting.DocCommentAlignment.SpaceAfterStar"/>
</rule>
```

### Custom
- Some others checks are made about array (`=>` alignments and indentation)

```
<rule ref="SymfonyCustom.Array.ArrayDeclaration"/>
```

- Do not use spaces after `(`, `{` or `[` and before `)`, `}` or `]`

```
<rule ref="SymfonyCustom.WhiteSpace.CloseBracketSpacing"/>
<rule ref="SymfonyCustom.WhiteSpace.OpenBracketSpacing"/>
```

- Do not use blank lines after class openers `{`

```
<rule ref="SymfonyCustom.Classes.ClassDeclaration"/>
```

- Do not use multiple following blank lines

```
<rule ref="SymfonyCustom.WhiteSpace.EmptyLines"/>
```

- Methods have scope modifier

```
<rule ref="SymfonyCustom.Scope.MethodScope"/>
```

- Member var should have phpDoc with one blank line before

```
<rule ref="SymfonyCustom.Commenting.VariableComment"/>
```

- `use` keywords should be alphabetically sorted

```
<rule ref="SymfonyCustom.Namespaces.AlphabeticallySortedUse"/>
```

- Unused `use` statement should be removed

```
<rule ref="SymfonyCustom.Namespaces.UnusedUse"/>
```

- Add a single space around comment tag (`@var`, `@return`, `...`)

```
<rule ref="SymfonyCustom.WhiteSpace.DocCommentTagSpacing"/>
```

- Add a single space before `namespace` declaration

```
<rule ref="SymfonyCustom.Namespaces.NamespaceDeclaration"/>
```
