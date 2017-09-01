Specifying Custom User Checker
==============================

If you need to specify custom user checker for the certain firewall area you need to [implement it](https://symfony.com/doc/3.2/security/user_checkers.html), then to add to `security.yml`:

``` yaml
# app/config/security.yml
security:
    # ...
    some_area:
        # ...
        fos_oauth: { user_checker: "<your UserCheckerInterface implementation>"
        # ...
```
