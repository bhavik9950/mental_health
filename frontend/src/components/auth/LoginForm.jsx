/**
 * Login Form Component
 * 
 * User authentication form with email/password login,
 * remember me functionality, and social login options
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import './LoginForm.css';

const LoginForm = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    rememberMe: false
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  
  const { login } = useAuth();
  const navigate = useNavigate();

  /**
   * TODO: Implement form validation
   * - Email format validation
   * - Password strength requirements
   * - Real-time validation feedback
   * - Accessibility improvements
   */
  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.email) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid';
    }
    
    if (!formData.password) {
      newErrors.password = 'Password is required';
    } else if (formData.password.length < 6) {
      newErrors.password = 'Password must be at least 6 characters';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  /**
   * TODO: Implement login functionality
   * - API call to authentication endpoint
   * - JWT token handling
   * - Error handling for invalid credentials
   * - Account lockout after failed attempts
   * - Loading states and animations
   */
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }
    
    setLoading(true);
    
    try {
      // TODO: Implement actual login logic
      // const response = await authService.login(formData);
      // await login(response.user, response.accessToken, response.refreshToken);
      // navigate('/dashboard');
      
      // Placeholder for development
      console.log('Login attempt:', formData);
      
      // TODO: Remove this placeholder and implement real authentication
      setTimeout(() => {
        setLoading(false);
        alert('Login functionality - TODO: Implement actual authentication');
      }, 1000);
      
    } catch (error) {
      // TODO: Implement proper error handling
      console.error('Login error:', error);
      setErrors({ general: 'Login failed. Please try again.' });
      setLoading(false);
    }
  };

  /**
   * TODO: Implement social login
   * - Google OAuth integration
   * - Facebook login
   * - Apple ID (for iOS)
   * - Handle OAuth callbacks
   */
  const handleSocialLogin = (provider) => {
    // TODO: Implement social login logic
    console.log(`Social login with ${provider} - TODO: Implement`);
  };

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
    
    // Clear specific field error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  return (
    <div className="login-form-container">
      <div className="login-form-card">
        <div className="login-header">
          <h1>Welcome Back</h1>
          <p>Sign in to your mental health support account</p>
        </div>

        {errors.general && (
          <div className="error-message general-error">
            {errors.general}
          </div>
        )}

        <form onSubmit={handleSubmit} className="login-form">
          <div className="form-group">
            <label htmlFor="email">Email Address</label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleInputChange}
              className={errors.email ? 'error' : ''}
              placeholder="Enter your email"
              disabled={loading}
            />
            {errors.email && <span className="error-text">{errors.email}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="password">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              value={formData.password}
              onChange={handleInputChange}
              className={errors.password ? 'error' : ''}
              placeholder="Enter your password"
              disabled={loading}
            />
            {errors.password && <span className="error-text">{errors.password}</span>}
          </div>

          <div className="form-options">
            <label className="checkbox-label">
              <input
                type="checkbox"
                name="rememberMe"
                checked={formData.rememberMe}
                onChange={handleInputChange}
                disabled={loading}
              />
              <span className="checkmark"></span>
              Remember me
            </label>
            
            <Link to="/forgot-password" className="forgot-password-link">
              Forgot Password?
            </Link>
          </div>

          <button 
            type="submit" 
            className="login-button"
            disabled={loading}
          >
            {loading ? 'Signing In...' : 'Sign In'}
          </button>
        </form>

        <div className="social-login">
          <div className="divider">
            <span>Or continue with</span>
          </div>
          
          <div className="social-buttons">
            <button 
              type="button"
              className="social-button google"
              onClick={() => handleSocialLogin('google')}
              disabled={loading}
            >
              <svg className="social-icon">
                {/* Google icon SVG */}
              </svg>
              Google
            </button>
            
            <button 
              type="button"
              className="social-button facebook"
              onClick={() => handleSocialLogin('facebook')}
              disabled={loading}
            >
              <svg className="social-icon">
                {/* Facebook icon SVG */}
              </svg>
              Facebook
            </button>
          </div>
        </div>

        <div className="signup-prompt">
          <p>
            Don't have an account?{' '}
            <Link to="/register" className="signup-link">
              Sign up for free
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default LoginForm;