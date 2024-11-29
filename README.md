# attachmentAV for Wordpress - Protect blog from viruses, trojans, and other kinds of malware

Protect your blog from viruses, trojans, and other kinds of malware. The plugin sends all uploads to the attachmentAV API to scan for malware with Sophos and blocks infected files.

> This plugin requires a subscription and API key: [Get API key](https://attachmentav.com/subscribe/wordpress/)

## Screenshot

The Wordpress plugin scans uploads for viruses, trojans, and other kinds of malware. Infected files are rejected during the upload.

![attachmentAV protects from infected uploads](./plugin/assets/screenshot-1.png)

## Installation

> [Looking for more detailed installation instructions?](https://attachmentav.com/help/setup-guide/wordpress.html)

1. Install attachmentAV either via the WordPress.org plugin repository or by uploading the files to your server. (See instructions on [how to install a WordPress plugin](https://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/))
1. Activate attachmentAV.
1. Open the attachmentAV plugin settings tab.
1. Create a subscription for the attachmentAV API and enter the API key.

## Help and Feedback

Please contact [hello@attachmentav.com](mailto:hello@attachmentav.com) in case you need help or want to leave feedback. Alternatively, open an issue or send a PR.

## Development

Use the following command to spin up a development environment.

```
docker compose up
```

Optionally use `ngrok` to get a publicly reachable development domain.

```
ngrok http http://localhost:80
```