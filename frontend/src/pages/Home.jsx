import React from 'react';
import { Link as RouterLink } from 'react-router-dom';
import Container from '@mui/material/Container';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import Paper from '@mui/material/Paper';
import Grid from '@mui/material/Grid';
import VisibilityIcon from '@mui/icons-material/Visibility';
import DnsIcon from '@mui/icons-material/Dns';
import HttpIcon from '@mui/icons-material/Http';

function Home() {
  return (
    <Container maxWidth="md" sx={{ mt: 5, mb: 5 }} className="page-content">
      <Box sx={{ my: 4, textAlign: 'center' }}>
        <Typography variant="h2" component="h1" gutterBottom className="header-title" sx={{ display: 'flex', justifyContent: 'center' }}>
          <VisibilityIcon sx={{ fontSize: 40 }} />
          OwlEyes
        </Typography>
        <Typography variant="h5" component="h2" color="text.secondary" paragraph>
          A simple monitoring service for your websites and services
        </Typography>
        <Button 
          variant="contained" 
          color="primary" 
          size="large" 
          component={RouterLink} 
          to="/projects"
          sx={{ mt: 2 }}
        >
          View Projects
        </Button>
      </Box>

      <Grid container spacing={4} sx={{ mt: 4 }}>
        <Grid item xs={12} md={6}>
          <Paper
            elevation={3}
            sx={{
              p: 3,
              height: '100%',
              display: 'flex',
              flexDirection: 'column',
            }}
          >
            <Box sx={{ display: 'flex', mb: 2 }}>
              <DnsIcon color="primary" sx={{ mr: 1, fontSize: 24 }} />
              <Typography variant="h5" component="h3">
                Ping Monitors
              </Typography>
            </Box>
            <Typography paragraph>
              Monitor the availability of your servers and services by checking TCP/IP connectivity.
              Perfect for ensuring your database servers, APIs, and other network resources are up and running.
            </Typography>
          </Paper>
        </Grid>
        <Grid item xs={12} md={6}>
          <Paper
            elevation={3}
            sx={{
              p: 3,
              height: '100%',
              display: 'flex',
              flexDirection: 'column',
            }}
          >
            <Box sx={{ display: 'flex', mb: 2 }}>
              <HttpIcon color="primary" sx={{ mr: 1, fontSize: 24 }} />
              <Typography variant="h5" component="h3">
                Website Monitors
              </Typography>
            </Box>
            <Typography paragraph>
              Monitor your websites by checking HTTP/HTTPS connectivity, status codes, and content.
              Search for specific keywords to ensure your website content is correct and available.
            </Typography>
          </Paper>
        </Grid>
      </Grid>

      <Box sx={{ mt: 6, mb: 4 }}>
        <Typography variant="h4" component="h2" gutterBottom>
          Features
        </Typography>
        <Grid container spacing={2}>
          <Grid item xs={12} md={4}>
            <Typography variant="h6" gutterBottom>
              Project Management
            </Typography>
            <Typography>
              Organize your monitors into projects with tags for easy filtering and sorting.
            </Typography>
          </Grid>
          <Grid item xs={12} md={4}>
            <Typography variant="h6" gutterBottom>
              Visualizations
            </Typography>
            <Typography>
              View monitor results in different modes: list, calendar, or graph.
            </Typography>
          </Grid>
          <Grid item xs={12} md={4}>
            <Typography variant="h6" gutterBottom>
              Status Badges
            </Typography>
            <Typography>
              Use status badges in your documentation to show the real-time status of your services.
            </Typography>
          </Grid>
        </Grid>
      </Box>
    </Container>
  );
}

export default Home; 