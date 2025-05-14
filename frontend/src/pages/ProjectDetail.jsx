import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import {
  Container,
  Typography,
  Box,
  Grid,
  Card,
  CardContent,
  CardActions,
  Button,
  Chip,
  CircularProgress,
  Paper,
  Tabs,
  Tab,
  Divider,
  IconButton,
  Tooltip,
  Stack,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Alert,
  Snackbar
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import ErrorIcon from '@mui/icons-material/Error';

function ProjectDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [project, setProject] = useState(null);
  const [monitors, setMonitors] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [tabValue, setTabValue] = useState(0);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [notification, setNotification] = useState({
    open: false,
    message: '',
    severity: 'success'
  });

  useEffect(() => {
    // Mock data for now - would be replaced with actual API call
    const mockProject = {
      id: parseInt(id),
      label: `Project ${id}`,
      description: 'This is a detailed description of the project and its purpose.',
      createdAt: '2024-05-01T10:00:00Z',
      updatedAt: '2024-05-10T15:30:00Z',
      tags: ['production', 'critical', 'website']
    };

    const mockMonitors = [
      {
        id: 101,
        type: 'website',
        label: 'Main Website',
        url: 'https://example.com',
        status: true,
        lastChecked: '2024-05-12T09:45:00Z',
        responseTime: 245
      },
      {
        id: 102,
        type: 'ping',
        label: 'API Server',
        host: 'api.example.com',
        port: 443,
        status: true,
        lastChecked: '2024-05-12T09:50:00Z',
        responseTime: 35
      },
      {
        id: 103,
        type: 'website',
        label: 'Documentation Portal',
        url: 'https://docs.example.com',
        status: false,
        lastChecked: '2024-05-12T09:48:00Z',
        responseTime: 0
      }
    ];
    
    // Simulate API call
    setTimeout(() => {
      setProject(mockProject);
      setMonitors(mockMonitors);
      setLoading(false);
    }, 500);
    
    // Real API calls would look like this:
    // fetch(`http://localhost:8080/api/v1/projects/${id}`)
    //   .then(response => response.json())
    //   .then(data => {
    //     setProject(data.data);
    //   })
    //   .catch(err => {
    //     setError(err.message);
    //     setLoading(false);
    //   });
    
    // fetch(`http://localhost:8080/api/v1/projects/${id}/monitors`)
    //   .then(response => response.json())
    //   .then(data => {
    //     setMonitors(data.data);
    //     setLoading(false);
    //   })
    //   .catch(err => {
    //     setError(err.message);
    //     setLoading(false);
    //   });
  }, [id]);

  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  const handleDeleteClick = () => {
    setDeleteDialogOpen(true);
  };

  const handleDeleteCancel = () => {
    setDeleteDialogOpen(false);
  };

  const handleDeleteConfirm = async () => {
    try {
      setDeleteDialogOpen(false);
      
      console.log(`Attempting to delete project with ID: ${id}`);
      
      // Call the API to delete the project
      const response = await fetch(`${import.meta.env.VITE_API_URL || 'http://localhost:8080'}/api/v1/projects/${id}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json'
        }
      });

      console.log(`Delete API response status: ${response.status}`);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error(`Error response body: ${errorText}`);
        throw new Error(`Failed to delete project: ${response.statusText}`);
      }

      const responseData = await response.json();
      console.log('Delete API response data:', responseData);

      // Show success notification
      setNotification({
        open: true,
        message: 'Project deleted successfully',
        severity: 'success'
      });

      // Navigate back to the projects list after a short delay
      setTimeout(() => {
        navigate('/projects');
      }, 1500);

    } catch (err) {
      console.error('Error deleting project:', err);
      
      // Show error notification
      setNotification({
        open: true,
        message: err.message || 'Error deleting project',
        severity: 'error'
      });
    }
  };

  const handleNotificationClose = () => {
    setNotification(prev => ({ ...prev, open: false }));
  };

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
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 3 }}>
        <Box>
          <Typography variant="h4" component="h1" gutterBottom>
            {project.label}
          </Typography>
          <Typography variant="body1" sx={{ mb: 2 }}>
            {project.description}
          </Typography>
          <Stack direction="row" spacing={1} sx={{ mb: 2 }}>
            {project.tags && project.tags.map((tag, index) => (
              <Chip key={index} label={tag} size="small" />
            ))}
          </Stack>
        </Box>
        <Box>
          <Button
            variant="contained"
            startIcon={<AddIcon />}
            sx={{ mr: 1 }}
            component={Link}
            to={`/projects/${id}/monitors/new`}
          >
            Add Monitor
          </Button>
          <IconButton color="primary" sx={{ mr: 1 }} component={Link} to={`/projects/${id}/edit`}>
            <Tooltip title="Edit Project">
              <EditIcon />
            </Tooltip>
          </IconButton>
          <IconButton color="error" onClick={handleDeleteClick}>
            <Tooltip title="Delete Project">
              <DeleteIcon />
            </Tooltip>
          </IconButton>
        </Box>
      </Box>

      {/* Delete Confirmation Dialog */}
      <Dialog
        open={deleteDialogOpen}
        onClose={handleDeleteCancel}
        aria-labelledby="alert-dialog-title"
        aria-describedby="alert-dialog-description"
      >
        <DialogTitle id="alert-dialog-title">
          Confirm Project Deletion
        </DialogTitle>
        <DialogContent>
          <DialogContentText id="alert-dialog-description">
            Are you sure you want to delete the project "{project?.label}"? 
            This action cannot be undone, and all associated monitors will also be deleted.
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDeleteCancel}>Cancel</Button>
          <Button onClick={handleDeleteConfirm} color="error" autoFocus>
            Delete
          </Button>
        </DialogActions>
      </Dialog>

      {/* Notification Snackbar */}
      <Snackbar 
        open={notification.open} 
        autoHideDuration={6000} 
        onClose={handleNotificationClose}
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert 
          onClose={handleNotificationClose} 
          severity={notification.severity} 
          sx={{ width: '100%' }}
        >
          {notification.message}
        </Alert>
      </Snackbar>

      <Paper sx={{ width: '100%', mb: 3 }}>
        <Tabs 
          value={tabValue} 
          onChange={handleTabChange} 
          indicatorColor="primary"
          textColor="primary"
          centered
        >
          <Tab label="List View" />
          <Tab label="Calendar View" />
          <Tab label="Graph View" />
        </Tabs>
      </Paper>

      <Divider sx={{ mb: 3 }} />

      {tabValue === 0 && (
        <Grid container spacing={3}>
          {monitors.map((monitor) => (
            <Grid item xs={12} md={6} key={monitor.id}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Typography variant="h6" component="h2">
                      {monitor.label}
                    </Typography>
                    {monitor.status ? (
                      <Chip 
                        icon={<CheckCircleIcon />} 
                        label="Up" 
                        color="success" 
                        size="small" 
                        className="monitor-status-badge monitor-status-up"
                      />
                    ) : (
                      <Chip 
                        icon={<ErrorIcon />} 
                        label="Down" 
                        color="error" 
                        size="small"
                        className="monitor-status-badge monitor-status-down"
                      />
                    )}
                  </Box>
                  
                  <Typography variant="body2" sx={{ mt: 1 }}>
                    {monitor.type === 'website' ? (
                      <>URL: {monitor.url}</>
                    ) : (
                      <>Host: {monitor.host}:{monitor.port}</>
                    )}
                  </Typography>
                  
                  <Typography variant="body2" sx={{ mt: 1 }}>
                    Response Time: {monitor.status ? `${monitor.responseTime}ms` : 'N/A'}
                  </Typography>
                  
                  <Typography variant="body2" sx={{ mt: 1 }}>
                    Last Checked: {new Date(monitor.lastChecked).toLocaleString()}
                  </Typography>
                </CardContent>
                <CardActions>
                  <Button size="small" component={Link} to={`/monitors/${monitor.id}`}>
                    View Details
                  </Button>
                </CardActions>
              </Card>
            </Grid>
          ))}
        </Grid>
      )}

      {tabValue === 1 && (
        <Box sx={{ p: 3, textAlign: 'center' }}>
          <Typography variant="h6">Calendar View</Typography>
          <Typography variant="body2">
            Calendar view showing uptime over time would be displayed here
          </Typography>
        </Box>
      )}

      {tabValue === 2 && (
        <Box sx={{ p: 3, textAlign: 'center' }}>
          <Typography variant="h6">Graph View</Typography>
          <Typography variant="body2">
            Performance and response time graphs would be displayed here
          </Typography>
        </Box>
      )}
    </Container>
  );
}

export default ProjectDetail; 