import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import {
  Container,
  Typography,
  Box,
  Paper,
  Grid,
  Card,
  CardContent,
  Button,
  CircularProgress,
  Tabs,
  Tab,
  Divider,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  IconButton,
  Tooltip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Alert,
  Snackbar
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import ErrorIcon from '@mui/icons-material/Error';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';

function MonitorDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [monitor, setMonitor] = useState(null);
  const [checkHistory, setCheckHistory] = useState([]);
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
    const mockMonitor = {
      id: parseInt(id),
      projectId: 1,
      type: 'website',
      label: 'Main Website',
      url: 'https://example.com',
      checkStatus: true,
      keywords: ['welcome', 'login'],
      periodicity: 60,
      createdAt: '2024-05-01T10:00:00Z',
      updatedAt: '2024-05-10T15:30:00Z',
      status: true,
      lastChecked: '2024-05-12T09:45:00Z',
      responseTime: 245,
      badgeUrl: `/api/v1/monitors/${id}/badge`
    };

    const mockHistory = [
      {
        id: 1001,
        status: true,
        startTime: '2024-05-12T09:45:00Z',
        endTime: '2024-05-12T09:45:01Z',
        responseTime: 245
      },
      {
        id: 1002,
        status: true,
        startTime: '2024-05-12T08:45:00Z',
        endTime: '2024-05-12T08:45:01Z',
        responseTime: 232
      },
      {
        id: 1003,
        status: false,
        startTime: '2024-05-12T07:45:00Z',
        endTime: '2024-05-12T07:45:30Z',
        responseTime: 0,
        error: 'Connection timed out'
      },
      {
        id: 1004,
        status: true,
        startTime: '2024-05-12T06:45:00Z',
        endTime: '2024-05-12T06:45:01Z',
        responseTime: 215
      },
      {
        id: 1005,
        status: true,
        startTime: '2024-05-12T05:45:00Z',
        endTime: '2024-05-12T05:45:01Z',
        responseTime: 238
      }
    ];
    
    // Simulate API call
    setTimeout(() => {
      setMonitor(mockMonitor);
      setCheckHistory(mockHistory);
      setLoading(false);
    }, 500);
    
    // Real API calls would look like this:
    // fetch(`http://localhost:8080/api/v1/monitors/${id}`)
    //   .then(response => response.json())
    //   .then(data => {
    //     setMonitor(data.data);
    //   })
    //   .catch(err => {
    //     setError(err.message);
    //     setLoading(false);
    //   });
    
    // fetch(`http://localhost:8080/api/v1/monitors/${id}/status`)
    //   .then(response => response.json())
    //   .then(data => {
    //     setCheckHistory(data.data);
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
      
      console.log(`Attempting to delete monitor with ID: ${id}`);
      
      // Call the API to delete the monitor
      const response = await fetch(`${import.meta.env.VITE_API_URL || 'http://localhost:8080'}/api/v1/monitors/${id}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json'
        }
      });

      console.log(`Delete API response status: ${response.status}`);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error(`Error response body: ${errorText}`);
        throw new Error(`Failed to delete monitor: ${response.statusText}`);
      }

      const responseData = await response.json();
      console.log('Delete API response data:', responseData);

      // Show success notification
      setNotification({
        open: true,
        message: 'Monitor deleted successfully',
        severity: 'success'
      });

      // Navigate back to the project page after a short delay
      setTimeout(() => {
        navigate(`/projects/${monitor.projectId}`);
      }, 1500);

    } catch (err) {
      console.error('Error deleting monitor:', err);
      
      // Show error notification
      setNotification({
        open: true,
        message: err.message || 'Error deleting monitor',
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
      <Button 
        variant="outlined" 
        startIcon={<ArrowBackIcon />} 
        component={Link} 
        to={`/projects/${monitor.projectId}`}
        sx={{ mb: 2 }}
      >
        Back to Project
      </Button>

      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 3 }}>
        <Box>
          <Typography variant="h4" component="h1" gutterBottom>
            {monitor.label}
          </Typography>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 2 }}>
            <Typography variant="body1">
              Status:
            </Typography>
            {monitor.status ? (
              <Chip 
                icon={<CheckCircleIcon />} 
                label="Up" 
                color="success" 
                className="monitor-status-badge monitor-status-up"
              />
            ) : (
              <Chip 
                icon={<ErrorIcon />} 
                label="Down" 
                color="error" 
                className="monitor-status-badge monitor-status-down"
              />
            )}
          </Box>
        </Box>
        <Box>
          <IconButton color="primary" sx={{ mr: 1 }} component={Link} to={`/monitors/${id}/edit`}>
            <Tooltip title="Edit Monitor">
              <EditIcon />
            </Tooltip>
          </IconButton>
          <IconButton color="error" onClick={handleDeleteClick}>
            <Tooltip title="Delete Monitor">
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
          Confirm Monitor Deletion
        </DialogTitle>
        <DialogContent>
          <DialogContentText id="alert-dialog-description">
            Are you sure you want to delete the monitor "{monitor?.label}"? 
            This action cannot be undone, and all associated check history will also be deleted.
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

      <Grid container spacing={3} sx={{ mb: 3 }}>
        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Details
              </Typography>
              <Divider sx={{ mb: 2 }} />
              <Box sx={{ display: 'grid', gridTemplateColumns: '1fr 1fr', rowGap: 1 }}>
                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Type:</Typography>
                <Typography variant="body2">{monitor.type}</Typography>

                {monitor.type === 'website' && (
                  <>
                    <Typography variant="body2" sx={{ fontWeight: 'bold' }}>URL:</Typography>
                    <Typography variant="body2">{monitor.url}</Typography>
                  </>
                )}

                {monitor.type === 'ping' && (
                  <>
                    <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Host:</Typography>
                    <Typography variant="body2">{monitor.host}</Typography>
                    
                    <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Port:</Typography>
                    <Typography variant="body2">{monitor.port}</Typography>
                  </>
                )}

                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Check Interval:</Typography>
                <Typography variant="body2">{monitor.periodicity} seconds</Typography>

                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Created:</Typography>
                <Typography variant="body2">{new Date(monitor.createdAt).toLocaleString()}</Typography>

                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Last Updated:</Typography>
                <Typography variant="body2">{new Date(monitor.updatedAt).toLocaleString()}</Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Latest Check
              </Typography>
              <Divider sx={{ mb: 2 }} />
              <Box sx={{ display: 'grid', gridTemplateColumns: '1fr 1fr', rowGap: 1 }}>
                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Status:</Typography>
                <Typography variant="body2">
                  {monitor.status ? (
                    <span style={{ color: 'green' }}>Up</span>
                  ) : (
                    <span style={{ color: 'red' }}>Down</span>
                  )}
                </Typography>

                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Response Time:</Typography>
                <Typography variant="body2">
                  {monitor.status ? `${monitor.responseTime}ms` : 'N/A'}
                </Typography>

                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Last Checked:</Typography>
                <Typography variant="body2">{new Date(monitor.lastChecked).toLocaleString()}</Typography>

                <Typography variant="body2" sx={{ fontWeight: 'bold' }}>Status Badge:</Typography>
                <Typography variant="body2">
                  <Link to={monitor.badgeUrl} target="_blank">
                    View Badge
                  </Link>
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Paper sx={{ width: '100%', mb: 3 }}>
        <Tabs 
          value={tabValue} 
          onChange={handleTabChange} 
          indicatorColor="primary"
          textColor="primary"
          centered
        >
          <Tab label="History" />
          <Tab label="Calendar View" />
          <Tab label="Graph View" />
        </Tabs>
      </Paper>

      <Divider sx={{ mb: 3 }} />

      {tabValue === 0 && (
        <TableContainer component={Paper}>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Date & Time</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Response Time</TableCell>
                <TableCell>Duration</TableCell>
                <TableCell>Error Message</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {checkHistory.map((check) => (
                <TableRow key={check.id}>
                  <TableCell>{new Date(check.startTime).toLocaleString()}</TableCell>
                  <TableCell>
                    {check.status ? (
                      <Chip icon={<CheckCircleIcon />} label="Up" size="small" color="success" />
                    ) : (
                      <Chip icon={<ErrorIcon />} label="Down" size="small" color="error" />
                    )}
                  </TableCell>
                  <TableCell>{check.status ? `${check.responseTime}ms` : 'N/A'}</TableCell>
                  <TableCell>
                    {check.endTime && check.startTime ? 
                      `${((new Date(check.endTime) - new Date(check.startTime)) / 1000).toFixed(1)}s` : 
                      'N/A'}
                  </TableCell>
                  <TableCell>{check.error || '-'}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
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

export default MonitorDetail; 