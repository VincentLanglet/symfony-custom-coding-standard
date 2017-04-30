# Coding Standard Rules
## From PSR2

We imported the [PSR2 Standard](./psr2.md) with this override:

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

## From symfony

We mainly respect the [Symfony Standard](./symfony.md) but

- We do not respect these rules:

  - Add no space around the concatenation (`.`) operator
  - Declare all the arguments on the same line as the method/function name, no matter how many arguments there are

- We do not currently check these rules:

  - Always use identical comparison unless you need type juggling
  - Use Yoda conditions when checking a variable against an expression
  - Exception and error message strings must be concatenated using `sprintf`
  - Calls to `trigger_error` with type `E_USER_DEPRECATED` must be switched to opt-in via `@`operator
  - Do not use `else`, `elseif`, `break` after `if` and `case` conditions which return or throw something

## Others
### Imported
- Do not use `<?` to define a php file

```
<rule ref="Generic.PHP.DisallowShortOpenTag"/>
```

- Add a single space after type casting

```
<rule ref="Generic.Formatting.SpaceAfterCast"/>
```

- Use lowercase for PHP functions

```
<rule ref="Squiz.PHP.LowercasePHPFunctions"/>
```

- Variable have scope modifier

```
<rule ref="Squiz.Scope.MemberVarScope"/>
<rule ref="Symfony3Custom.Scope.MethodScope"/>
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

### Custom
- Some others checks are made about array (`=>` alignments and indentation)

```
<rule ref="Symfony3Custom.Array.ArrayDeclaration"/>
```

- Do not use spaces after `(` or `{` and before `)` or `}`

```
<rule ref="Symfony3Custom.WhiteSpace.CloseBracketSpacing"/>
<rule ref="Symfony3Custom.WhiteSpace.OpenBracketSpacing"/>
```

- Do not use multiple following blank lines

```
<rule ref="Symfony3Custom.WhiteSpace.EmptyLines"/>
```

- Methods have scope modifier

```
<rule ref="Symfony3Custom.Scope.MethodScope"/>
```

- Member var should have phpDoc with one blank line before

```
<rule ref="Symfony3Custom.Commenting.VariableComment"/>
```
