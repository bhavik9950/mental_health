/**
 * Admin Dashboard Component
 * 
 * Main administrative interface with overview statistics,
 * user management, content moderation, and system monitoring
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { Navigate } from 'react-router-dom';
import StatsCard from './StatsCard';
import UserManagement from './UserManagement';
import ContentModeration from './ContentModeration';
import Analytics from './Analytics';
import SystemSettings from './SystemSettings';
import AdminProfile from './AdminProfile';
import './AdminDashboard.css';

const AdminDashboard = () => {
  const { user, isAdmin } = useAuth();
  const [activeTab, setActiveTab] = useState('overview');
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);

  // Redirect if not admin
  if (!isAdmin) {
    return <Navigate to="/unauthorized" replace />;
  }

  /**
   * TODO: Implement dashboard data fetching
   * - User statistics
   * - Content metrics
   * - System health
   * - Recent activity
   * - Mental health trends
   */
  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        // TODO: Implement API call
        // const response = await adminService.getDashboardData();
        // setDashboardData(response.data);
        
        // Placeholder data for development
        setTimeout(() => {
          setDashboardData({
            userStats: {
              totalUsers: 1250,
              activeUsers: 890,
              newUsersToday: 12,
              newUsersThisWeek: 85
            },
            contentStats: {
              totalPosts: 3400,
              pendingModeration: 25,
              reportedContent: 8,
              responsesToday: 45
            },
            systemStats: {
              uptime: '99.9%',
              responseTime: '120ms',
              errorRate: '0.1%',
              storageUsed: '2.5GB'
            }
          });
          setLoading(false);
        }, 1000);
        
      } catch (error) {
        console.error('Failed to fetch dashboard data:', error);
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  /**
   * TODO: Implement auto-refresh functionality
   * - Real-time updates
   * - WebSocket connections
   * - Notification system
   */
  useEffect(() => {
    const interval = setInterval(() => {
      // Auto-refresh dashboard data every 5 minutes
      // TODO: Implement refresh logic
    }, 300000);

    return () => clearInterval(interval);
  }, []);

  const tabs = [
    { id: 'overview', label: 'Overview', icon: '📊' },
    { id: 'users', label: 'Users', icon: '👥' },
    { id: 'moderation', label: 'Moderation', icon: '🛡️' },
    { id: 'analytics', label: 'Analytics', icon: '📈' },
    { id: 'settings', label: 'Settings', icon: '⚙️' },
    { id: 'profile', label: 'Profile', icon: '👤' }
  ];

  if (loading) {
    return (
      <div className="admin-dashboard-loading">
        <div className="loading-spinner"></div>
        <p>Loading admin dashboard...</p>
      </div>
    );
  }

  return (
    <div className="admin-dashboard">
      <header className="admin-header">
        <div className="admin-header-content">
          <h1>Admin Dashboard</h1>
          <div className="admin-welcome">
            <span>Welcome back, {user?.full_name || user?.username}</span>
            <div className="admin-status-indicator online"></div>
          </div>
        </div>
      </header>

      <div className="admin-dashboard-content">
        <nav className="admin-sidebar">
          <ul className="admin-nav-list">
            {tabs.map(tab => (
              <li key={tab.id}>
                <button
                  className={`admin-nav-item ${activeTab === tab.id ? 'active' : ''}`}
                  onClick={() => setActiveTab(tab.id)}
                >
                  <span className="nav-icon">{tab.icon}</span>
                  <span className="nav-label">{tab.label}</span>
                </button>
              </li>
            ))}
          </ul>
        </nav>

        <main className="admin-main-content">
          {activeTab === 'overview' && (
            <div className="dashboard-overview">
              <div className="stats-grid">
                <StatsCard
                  title="Total Users"
                  value={dashboardData?.userStats.totalUsers || 0}
                  change="+12%"
                  trend="up"
                  icon="👥"
                  color="blue"
                />
                <StatsCard
                  title="Active Users"
                  value={dashboardData?.userStats.activeUsers || 0}
                  change="+5%"
                  trend="up"
                  icon="🟢"
                  color="green"
                />
                <StatsCard
                  title="Pending Moderation"
                  value={dashboardData?.contentStats.pendingModeration || 0}
                  change="-8%"
                  trend="down"
                  icon="🛡️"
                  color="orange"
                />
                <StatsCard
                  title="System Uptime"
                  value={dashboardData?.systemStats.uptime || '0%'}
                  change="+0.1%"
                  trend="up"
                  icon="⚡"
                  color="purple"
                />
              </div>

              <div className="overview-widgets">
                <div className="widget">
                  <h3>Recent Activity</h3>
                  <div className="activity-list">
                    {/* TODO: Implement recent activity feed */}
                    <div className="activity-item">
                      <span className="activity-icon">👤</span>
                      <span className="activity-text">New user registration</span>
                      <span className="activity-time">2 minutes ago</span>
                    </div>
                    <div className="activity-item">
                      <span className="activity-icon">📝</span>
                      <span className="activity-text">Content reported for review</span>
                      <span className="activity-time">5 minutes ago</span>
                    </div>
                  </div>
                </div>

                <div className="widget">
                  <h3>Quick Actions</h3>
                  <div className="quick-actions">
                    <button 
                      className="quick-action-btn"
                      onClick={() => setActiveTab('moderation')}
                    >
                      Review Content
                    </button>
                    <button 
                      className="quick-action-btn"
                      onClick={() => setActiveTab('users')}
                    >
                      Manage Users
                    </button>
                    <button 
                      className="quick-action-btn"
                      onClick={() => setActiveTab('analytics')}
                    >
                      View Analytics
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'users' && <UserManagement />}
          {activeTab === 'moderation' && <ContentModeration />}
          {activeTab === 'analytics' && <Analytics />}
          {activeTab === 'settings' && <SystemSettings />}
          {activeTab === 'profile' && <AdminProfile />}
        </main>
      </div>
    </div>
  );
};

export default AdminDashboard;