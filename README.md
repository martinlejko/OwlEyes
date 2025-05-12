# OwlEyes - Simple Monitoring Service

A monitoring service similar to UptimeRobot that allows users to create projects and monitors for checking website availability and ping status.

## Description

OwlEyes is a web application that lets you monitor the availability of websites and services. It provides a dashboard to visualize the status of your monitors and get alerted when something goes down.

### Features

- **Project Management**: Organize monitors into projects with labels, descriptions, and tags
- **Monitor Types**:
  - **Ping Monitors**: Check if a server is reachable via TCP connection
  - **Website Monitors**: Check if a website is available and contains specific keywords
- **Visualizations**:
  - List view of monitor results
  - Calendar view showing uptime percentage by day
  - Graph view of response times
- **APIs**:
  - RESTful API for programmatic access
  - GraphQL API for flexible queries
- **Status Badges**: Embed monitor status badges in your documentation

## Technology Stack

- **Backend**:
  - PHP 8.1 with Slim 4 Framework
  - Doctrine ORM for database access
  - PostgreSQL database
  - Monolog for logging
  - PHP-DI for dependency injection
  - GraphQL with webonyx/graphql-php
  
- **Frontend**:
  - React 18 with React Router
  - Material UI for components
  - Chart.js for data visualization
  - FullCalendar for calendar view
  - React Query for data fetching
  
- **Infrastructure**:
  - Docker and Docker Compose for containerization
  - Nginx as web server

## Installation

1. Clone the repository:

```bash
git clone https://github.com/martinlejko/OwlEyes.git .
```

2. Run the setup script:

```bash
chmod +x setup.sh
./setup.sh
```

3. Start the application:

```bash
docker compose up -d
```

4. The application will be available at:
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8080

## Usage

### Creating a Project

1. Navigate to the Projects page
2. Click "Add Project"
3. Enter project details (label, description, tags)
4. Submit the form

### Adding a Monitor

1. Open a project
2. Click "Add Monitor"
3. Select the monitor type (ping or website)
4. Configure the monitor settings
   - For ping monitors: host and port
   - For website monitors: URL, status check, keywords
5. Set the monitoring interval (5-300 seconds)
6. Submit the form

### Viewing Monitor Status

1. Open a monitor from the project page
2. View the status in different visualization modes:
   - List view: Shows a paginated list of status checks
   - Calendar view: Shows status by day with color coding
   - Graph view: Shows response time trends

### Using Status Badges

Each monitor has a badge URL that you can embed in your documentation:

```
![Monitor Status](http://localhost:8080/api/v1/monitors/{id}/badge)
```

## API Documentation

- REST API documentation is available at: http://localhost:8080/docs
- GraphQL API is accessible at: http://localhost:8080/graphql

## License

This project is open source, available under the MIT license.

## Acknowledgements

- [Slim Framework](https://www.slimframework.com/)
- [Doctrine ORM](https://www.doctrine-project.org/)
- [React](https://reactjs.org/)
- [Material UI](https://mui.com/)
- [Vite](https://vitejs.dev/)
