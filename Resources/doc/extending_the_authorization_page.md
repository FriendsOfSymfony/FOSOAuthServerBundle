Extending the Authorization page
================================

The "Authorization Page" is the page you will present to your users, in order to ask them to share their
data with a third-party consumer. This is most of the time a page with two buttons _Deny_, and _Allow_.

By default, the FOSOAuthServerBundle's authorization page is really basic, and it's probably a good idea to improve it.

The first step is to copy the [`authorize_content.html.twig`](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/blob/master/Resources/views/Authorize/authorize_content.html.twig) template to the `app/Resources/FOSOAuthServerBundle/views/Authorize/` directory.

You're almost done, now you just have to customize it. By default a _client_ in the FOSOAuthServerBundle
doesn't have any _name_ or _title_ because it depends on your application, and what you really need.
But most of the time, you will give a name to each of your clients, this part is described in the [extending the model](extending_the_model.md) section.

Now, assuming your clients have a nice _name_, it's a pretty nice idea to expose it to your
users. That way, they will know which consumer asks for their data. If you take a look at the `AuthorizeController`,
you will see a `client` variable which is passed to the templating engine. That means you can use it in your custom template:

``` html+jinja
<h2>The application "{{ client.name }}" would like to connect to your account</h2>

{{ form_start(form, {'method': 'POST', 'action': path('fos_oauth_server_authorize'), 'label_attr': {'class': 'fos_oauth_server_authorize'} }) }}
    <input class="btn" type="submit" name="rejected" value="{{ 'authorize.reject'|trans({}, 'FOSOAuthServerBundle') }}" />
    <input class="btn btn-primary" type="submit" name="accepted" value="{{ 'authorize.accept'|trans({}, 'FOSOAuthServerBundle') }}" />

    {{ form_row(form.client_id) }}
    {{ form_row(form.response_type) }}
    {{ form_row(form.redirect_uri) }}
    {{ form_row(form.state) }}
    {{ form_row(form.scope) }}
    {{ form_rest(form) }}
</form>
```

[Back to index](index.md)
