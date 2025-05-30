"use client";
import React, { useState } from 'react';
// Using simple text icons instead of lucide-react

const CodeReviewChecklist = () => {
  const [checkedItems, setCheckedItems] = useState({});
  const [expandedSections, setExpandedSections] = useState({});

  const toggleCheck = (id) => {
    setCheckedItems(prev => ({
      ...prev,
      [id]: !prev[id]
    }));
  };

  const toggleSection = (section) => {
    setExpandedSections(prev => ({
      ...prev,
      [section]: !prev[section]
    }));
  };

  const getProgress = (items) => {
    const checkedCount = items.filter(item => checkedItems[item.id]).length;
    return Math.round((checkedCount / items.length) * 100);
  };

  const CheckItem = ({ id, text, priority = 'normal', tip = null }) => (
    <div className={`flex items-start gap-3 p-3 rounded-lg border ${
      priority === 'critical' ? 'border-red-200 bg-red-50' :
      priority === 'high' ? 'border-orange-200 bg-orange-50' : 
      'border-gray-200 bg-gray-50'
    }`}>
      <button
        onClick={() => toggleCheck(id)}
        className="mt-1 flex-shrink-0"
      >
        <span className={`text-xl ${checkedItems[id] ? 'text-green-600' : 'text-gray-400'}`}>
          {checkedItems[id] ? '✅' : '⭕'}
        </span>
      </button>
      <div className="flex-1">
        <span className={`${checkedItems[id] ? 'line-through text-gray-500' : ''}`}>
          {text}
        </span>
        {priority === 'critical' && <span className="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded">CRITICAL</span>}
        {priority === 'high' && <span className="ml-2 text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">HIGH</span>}
        {tip && <div className="text-sm text-gray-600 mt-1 italic">{tip}</div>}
      </div>
    </div>
  );

  const sections = [
    {
      id: 'security',
      title: 'Security & Validation',
      icon: '🛡️',
      color: 'red',
      items: [
        { id: 'sec1', text: 'SQL injection prevention (prepared statements, parameterized queries)', priority: 'critical', tip: 'Check all database queries use mysqli_prepare() or PDO with placeholders' },
        { id: 'sec2', text: 'XSS protection (htmlspecialchars, ENT_QUOTES on all outputs)', priority: 'critical', tip: 'Every user input displayed should be escaped' },
        { id: 'sec3', text: 'CSRF protection (tokens in forms)', priority: 'high', tip: 'Add hidden CSRF tokens to all forms and verify on submission' },
        { id: 'sec4', text: 'Session management (session_start, proper logout)', priority: 'high' },
        { id: 'sec5', text: 'Input validation (server-side for all form data)', priority: 'high', tip: 'Never trust client-side validation alone' },
        { id: 'sec6', text: 'File upload security (if applicable)', priority: 'high' },
        { id: 'sec7', text: 'Authentication checks on protected pages', priority: 'critical' }
      ]
    },
    {
      id: 'structure',
      title: 'Code Structure & Organization',
      icon: '💻',
      color: 'blue',
      items: [
        { id: 'str1', text: 'Proper separation of concerns (logic vs presentation)', priority: 'normal' },
        { id: 'str2', text: 'Consistent file naming and organization', priority: 'normal' },
        { id: 'str3', text: 'Include files properly structured (header, sidebar, footer, scripts)', priority: 'normal' },
        { id: 'str4', text: 'Configuration file for database settings', priority: 'normal', tip: 'Keep DB credentials separate and secure' },
        { id: 'str5', text: 'Error handling and logging', priority: 'high' },
        { id: 'str6', text: 'Clean, readable code with proper indentation', priority: 'normal' },
        { id: 'str7', text: 'Comments for complex logic', priority: 'normal' }
      ]
    },
    {
      id: 'database',
      title: 'Database & Data Handling',
      icon: '🗃️',
      color: 'green',
      items: [
        { id: 'db1', text: 'Proper database connection handling (mysqli or PDO)', priority: 'high' },
        { id: 'db2', text: 'Connection closing after operations', priority: 'normal' },
        { id: 'db3', text: 'Proper error handling for database operations', priority: 'high' },
        { id: 'db4', text: 'Efficient queries (avoid N+1 problems)', priority: 'normal', tip: 'Check for queries inside loops' },
        { id: 'db5', text: 'Data sanitization before database operations', priority: 'high' },
        { id: 'db6', text: 'Proper use of transactions where needed', priority: 'normal' }
      ]
    },
    {
      id: 'frontend',
      title: 'Frontend & UI/UX',
      icon: '📱',
      color: 'purple',
      items: [
        { id: 'fe1', text: 'Bootstrap 5 classes used correctly', priority: 'normal' },
        { id: 'fe2', text: 'Responsive design on all screen sizes', priority: 'high', tip: 'Test on mobile, tablet, and desktop' },
        { id: 'fe3', text: 'Proper modal implementation', priority: 'normal' },
        { id: 'fe4', text: 'Form validation (both client and server side)', priority: 'high' },
        { id: 'fe5', text: 'Loading states for AJAX operations', priority: 'normal' },
        { id: 'fe6', text: 'Consistent styling and spacing', priority: 'normal' },
        { id: 'fe7', text: 'Accessibility (alt tags, proper labels, keyboard navigation)', priority: 'normal' }
      ]
    },
    {
      id: 'javascript',
      title: 'JavaScript & AJAX',
      icon: '⚡',
      color: 'yellow',
      items: [
        { id: 'js1', text: 'AJAX error handling implemented', priority: 'high' },
        { id: 'js2', text: 'Proper jQuery usage (event delegation, document ready)', priority: 'normal' },
        { id: 'js3', text: 'SweetAlert implementation for user feedback', priority: 'normal' },
        { id: 'js4', text: 'Form data serialization handled correctly', priority: 'normal' },
        { id: 'js5', text: 'No inline JavaScript (separate files)', priority: 'normal' },
        { id: 'js6', text: 'CSRF tokens included in AJAX requests', priority: 'high' },
        { id: 'js7', text: 'Loading indicators during AJAX calls', priority: 'normal' }
      ]
    }
  ];

  const allItems = sections.flatMap(section => section.items);
  const overallProgress = getProgress(allItems);
  const criticalItems = allItems.filter(item => item.priority === 'critical');
  const criticalProgress = getProgress(criticalItems);

  return (
    <div className="max-w-4xl mx-auto p-6 bg-white">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">PHP Admin Dashboard Code Review</h1>
        <p className="text-gray-600">Comprehensive checklist for reviewing complaint management system code</p>
        
        <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-sm font-medium text-blue-900">Overall Progress</span>
              <span className="text-sm text-blue-700">{overallProgress}%</span>
            </div>
            <div className="w-full bg-blue-200 rounded-full h-2">
              <div 
                className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                style={{ width: `${overallProgress}%` }}
              ></div>
            </div>
          </div>
          
          <div className="bg-red-50 border border-red-200 rounded-lg p-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-sm font-medium text-red-900">Critical Items</span>
              <span className="text-sm text-red-700">{criticalProgress}%</span>
            </div>
            <div className="w-full bg-red-200 rounded-full h-2">
              <div 
                className="bg-red-600 h-2 rounded-full transition-all duration-300" 
                style={{ width: `${criticalProgress}%` }}
              ></div>
            </div>
          </div>
        </div>
      </div>

      <div className="space-y-4">
        {sections.map((section) => {
          const progress = getProgress(section.items);
          const isExpanded = expandedSections[section.id];
          
          return (
            <div key={section.id} className="border border-gray-200 rounded-lg overflow-hidden">
              <button
                onClick={() => toggleSection(section.id)}
                className={`w-full px-6 py-4 bg-${section.color}-50 hover:bg-${section.color}-100 flex items-center justify-between transition-colors`}
              >
                <div className="flex items-center gap-3">
                  {section.icon}
                  <span className="font-semibold text-gray-900">{section.title}</span>
                  <span className="text-sm text-gray-500">({section.items.length} items)</span>
                </div>
                <div className="flex items-center gap-3">
                  <div className="text-right">
                    <div className="text-sm font-medium">{progress}%</div>
                    <div className="w-20 bg-gray-200 rounded-full h-2">
                      <div 
                        className={`bg-${section.color}-600 h-2 rounded-full transition-all duration-300`}
                        style={{ width: `${progress}%` }}
                      ></div>
                    </div>
                  </div>
                  <div className={`transform transition-transform ${isExpanded ? 'rotate-180' : ''}`}>
                    ▼
                  </div>
                </div>
              </button>
              
              {isExpanded && (
                <div className="p-6 space-y-3">
                  {section.items.map((item) => (
                    <CheckItem key={item.id} {...item} />
                  ))}
                </div>
              )}
            </div>
          );
        })}
      </div>

      <div className="mt-8 p-6 bg-gray-50 rounded-lg">
        <h3 className="font-semibold text-gray-900 mb-3 flex items-center gap-2">
          <span className="text-xl">⚠️</span>
          Quick Security Reminders
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
          <div>
            <strong>For PHP:</strong> Always use prepared statements, validate inputs server-side, escape outputs with htmlspecialchars()
          </div>
          <div>
            <strong>For AJAX:</strong> Include CSRF tokens, handle errors gracefully, show loading states
          </div>
        </div>
      </div>
    </div>
  );
};

export default CodeReviewChecklist;