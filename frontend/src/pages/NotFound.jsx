import React from 'react';
import { Link as RouterLink } from 'react-router-dom';
import Container from '@mui/material/Container';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import SentimentDissatisfiedIcon from '@mui/icons-material/SentimentDissatisfied';

function NotFound() {
  return (
    <Container maxWidth="md" className="page-content">
      <Box sx={{ my: 4, textAlign: 'center' }}>
        <SentimentDissatisfiedIcon sx={{ fontSize: 80, mb: 2, color: 'text.secondary' }} />
        <Typography variant="h3" component="h1" gutterBottom>
          404 - Page Not Found
        </Typography>
        <Typography variant="h5" component="h2" color="text.secondary" paragraph>
          Sorry, the page you are looking for does not exist.
        </Typography>
        <Button 
          variant="contained" 
          color="primary" 
          component={RouterLink} 
          to="/"
          sx={{ mt: 2 }}
        >
          Go to Home
        </Button>
      </Box>
    </Container>
  );
}

export default NotFound; 