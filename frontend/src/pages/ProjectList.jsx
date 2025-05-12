import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Container, 
  Typography, 
  Box, 
  Grid, 
  Card, 
  CardContent, 
  CardActions,
  Button,
  CircularProgress,
  Fab,
  Chip,
  Stack
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';

function ProjectList() {
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Mock data for now - would be replaced with actual API call
    const mockProjects = [
      {
        id: 1,
        label: 'Website Monitoring',
        description: 'Monitoring for all company websites',
        monitorCount: 5,
        tags: ['production', 'website']
      },
      {
        id: 2,
        label: 'API Services',
        description: 'Monitoring for critical API endpoints',
        monitorCount: 8,
        tags: ['api', 'critical']
      },
      {
        id: 3,
        label: 'Database Servers',
        description: 'Database connection monitoring',
        monitorCount: 3,
        tags: ['database', 'production']
      }
    ];
    
    // Simulate API call
    setTimeout(() => {
      setProjects(mockProjects);
      setLoading(false);
    }, 500);
    
    // Real API call would look like this:
    // fetch('http://localhost:8080/api/v1/projects')
    //   .then(response => response.json())
    //   .then(data => {
    //     setProjects(data.data);
    //     setLoading(false);
    //   })
    //   .catch(err => {
    //     setError(err.message);
    //     setLoading(false);
    //   });
  }, []);

  if (loading) {
    return (
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }} className="page-content">
        <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '50vh' }}>
          <CircularProgress />
        </Box>
      </Container>
    );
  }

  if (error) {
    return (
      <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }} className="page-content">
        <Typography variant="h5" color="error" gutterBottom>
          Error: {error}
        </Typography>
      </Container>
    );
  }

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }} className="page-content">
      <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
        <Typography variant="h4" component="h1" gutterBottom>
          Projects
        </Typography>
        <Fab color="primary" aria-label="add" component={Link} to="/projects/new">
          <AddIcon />
        </Fab>
      </Box>
      
      <Grid container spacing={3}>
        {projects.map((project) => (
          <Grid item xs={12} md={4} key={project.id}>
            <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column' }} className="dashboard-card">
              <CardContent className="dashboard-card-content">
                <Typography variant="h5" component="h2" gutterBottom>
                  {project.label}
                </Typography>
                <Typography variant="body2" color="text.secondary" paragraph>
                  {project.description}
                </Typography>
                <Typography variant="body2">
                  Monitors: {project.monitorCount}
                </Typography>
                <Stack direction="row" spacing={1} sx={{ mt: 2 }}>
                  {project.tags && project.tags.map((tag, index) => (
                    <Chip key={index} label={tag} size="small" />
                  ))}
                </Stack>
              </CardContent>
              <CardActions>
                <Button size="small" component={Link} to={`/projects/${project.id}`}>
                  View Details
                </Button>
              </CardActions>
            </Card>
          </Grid>
        ))}
      </Grid>
    </Container>
  );
}

export default ProjectList; 