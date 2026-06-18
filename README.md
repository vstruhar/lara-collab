<p align="center">
  <img src="/resources/docs/banner.jpg" alt="LaraCollab Banner">
</p>

# LaraCollab

LaraCollab is a project management tool built with Laravel and React, designed specifically for developers and development companies. It provides a comprehensive platform for managing clients, projects, time tracking, and invoice generation—all for free.

## Why LaraCollab?

While numerous feature-rich project management tools exist, LaraCollab stands out by being purpose-built for Laravel developers. It offers seamless integration and extensive customization options, enabling teams to tailor the platform to their unique workflows.

## Features

- **User Management**: Comprehensive role-based access control (client, manager, developer, designer) with customizable permissions
- **Client Management**: Manage client companies and user access to company-specific tasks
- **Project Management**: Full project lifecycle management with granular user access control
- **Task Organization**: Group tasks by status (Todo, In Progress, QA, Done, Deployed)
- **Rich Task Features**: Assign tasks, set due dates, add custom labels, estimate time (manual or timer), attach files, subscribe users, and comment
- **Advanced Filtering**: Efficient task organization with powerful filtering options
- **Real-time Updates**: Live notifications and task updates via web sockets
- **Mentions**: Tag users in task descriptions and comments for better collaboration
- **Personal Dashboard**: Dedicated "My Tasks" page for individual users
- **Activity Tracking**: Monitor activity across all projects or specific ones
- **Invoice Generation**: Create invoices from billable tasks with logged time
- **Document Management**: Print or download invoices directly from the platform
- **Analytics Dashboard**: Track project progress, overdue tasks, recent assignments, and recent comments
- **Reporting**: Generate reports for daily logged time per user and total logged time
- **Dark Mode**: Full dark mode support for user preference

## Screenshots

<p align="center">
  <img src="/resources/docs/screenshots/Dashboard - light.jpeg" width="45%" alt="Dashboard Light">
  <img src="/resources/docs/screenshots/Dashboard - dark.jpeg" width="45%" alt="Dashboard Dark">
</p>

<p align="center">
  <img src="/resources/docs/screenshots/Projects - light.jpeg" width="45%" alt="Projects Light">
  <img src="/resources/docs/screenshots/Projects - dark.jpeg" width="45%" alt="Projects Dark">
</p>

<p align="center">
  <img src="/resources/docs/screenshots/Project tasks - light.jpeg" width="45%" alt="Project Tasks Light">
  <img src="/resources/docs/screenshots/Project tasks - dark.jpeg" width="45%" alt="Project Tasks Dark">
</p>

<p align="center">
  <img src="/resources/docs/screenshots/Task - light.jpeg" width="45%" alt="Task Light">
  <img src="/resources/docs/screenshots/Task - dark.jpeg" width="45%" alt="Task Dark">
</p>

<p align="center">
  <img src="/resources/docs/screenshots/My tasks - light.jpeg" width="45%" alt="My Tasks Light">
  <img src="/resources/docs/screenshots/My tasks - dark.jpeg" width="45%" alt="My Tasks Dark">
</p>

<p align="center">
  <img src="/resources/docs/screenshots/Activity - light.jpeg" width="45%" alt="Activity Light">
  <img src="/resources/docs/screenshots/Activity - dark.jpeg" width="45%" alt="Activity Dark">
</p>

<p align="center">
  <img src="/resources/docs/screenshots/Invoice - light.jpeg" width="45%" alt="Invoice Light">
  <img src="/resources/docs/screenshots/Invoice - dark.jpeg" width="45%" alt="Invoice Dark">
</p>

## Tech Stack

- **Backend**: [Laravel](https://laravel.com) - A robust PHP framework
- **Frontend**: [React](https://react.dev) - Modern JavaScript library for building user interfaces
- **Integration**: [Inertia.js](https://inertiajs.com) - Seamless Laravel-React integration
- **UI Components**: [Mantine](https://mantine.dev) - Comprehensive React component library

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and npm
- Database (MySQL, PostgreSQL, SQLite, or SQL Server)

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/vstruhar/lara-collab.git
   cd lara-collab
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up database**
   - Create an empty database for the application
   - Update your `.env` file with the database credentials (variables prefixed with `DB_`)

5. **Run migrations**
   ```bash
   php artisan migrate --seed
   ```
   - You'll be prompted to seed development data
   - Choose `yes` for development/testing
   - Choose `no` for production

6. **Create storage link**
   ```bash
   php artisan storage:link
   ```

### Development

Start the development server:
```bash
npm run dev
```

> **Note**: [Laravel Sail](https://laravel.com/docs/10.x/sail) was used during development. You may use it as an alternative to the standard Laravel setup.

### Production Deployment

1. **Install production dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm run build
   ```

2. **Optimize the application**
   ```bash
   php artisan optimize
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Set up task scheduler**
   Add the following entry to your crontab (edit with `crontab -e`):
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

4. **Configure queue worker** (optional)
   - Set `QUEUE_CONNECTION=database` in your `.env` file
   - Configure [Supervisor](https://laravel.com/docs/10.x/queues#supervisor-configuration) to run the queue worker:
     ```bash
     php artisan queue:work --queue=default,email
     ```

5. **Configure email**
   Update email settings in your `.env` file (variables with `MAIL_` prefix).

## Default Credentials

After running migrations with seed, a default admin user is created:

- **Email**: admin@mail.com
- **Password**: password

> **Important**: Change the default password immediately after your first login.

## Configuration

### Web Sockets

Configure real-time notifications using [Pusher](https://pusher.com) or an [open-source alternative](https://laravel.com/docs/10.x/broadcasting#open-source-alternatives).

**To use Pusher:**

1. Sign up for a Pusher account and create a project
2. Copy your app keys to the `.env` file (variables with `PUSHER_` prefix)

### Social Login (Google)

1. Set up the [OAuth consent screen](https://console.cloud.google.com/apis/credentials/consent) on Google Cloud Console
2. Create an [OAuth Client ID](https://console.cloud.google.com/apis/credentials) and select "Web application" as the type
3. Add the generated "Client ID" and "Client secret" to your `.env` file:
   - `GOOGLE_CLIENT_ID`
   - `GOOGLE_CLIENT_SECRET`

## Roadmap

### Completed
- [x] Kanban board view
- [x] Expense and profit reporting per user
- [x] Project notes section

### Planned
- [ ] Multi-user time logging per task
- [ ] Task change history
- [ ] Granular per-user permissions
- [ ] Responsive design improvements
- [ ] Emoji support in rich text editor
- [ ] Comprehensive test suite
- [ ] Performance optimization (frontend and backend)
- [ ] TypeScript migration

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues, questions, or contributions, please visit our [GitHub repository](https://github.com/vstruhar/lara-collab).