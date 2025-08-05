import React from 'react';
import {NavigationContainer} from '@react-navigation/native';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {createStackNavigator} from '@react-navigation/stack';
import {Provider as PaperProvider} from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';

// Screens
import InventoryScreen from './screens/InventoryScreen';
import AddMedicineScreen from './screens/AddMedicineScreen';
import EditMedicineScreen from './screens/EditMedicineScreen';
import ReportsScreen from './screens/ReportsScreen';
import SettingsScreen from './screens/SettingsScreen';

// Context
import {MedicineProvider} from './context/MedicineContext';

const Tab = createBottomTabNavigator();
const Stack = createStackNavigator();

const InventoryStack = () => {
  return (
    <Stack.Navigator>
      <Stack.Screen 
        name="InventoryList" 
        component={InventoryScreen} 
        options={{title: 'Medicine Inventory'}}
      />
      <Stack.Screen 
        name="AddMedicine" 
        component={AddMedicineScreen} 
        options={{title: 'Add Medicine'}}
      />
      <Stack.Screen 
        name="EditMedicine" 
        component={EditMedicineScreen} 
        options={{title: 'Edit Medicine'}}
      />
    </Stack.Navigator>
  );
};

const App = () => {
  return (
    <PaperProvider>
      <MedicineProvider>
        <NavigationContainer>
          <Tab.Navigator
            screenOptions={({route}) => ({
              tabBarIcon: ({focused, color, size}) => {
                let iconName;

                if (route.name === 'Inventory') {
                  iconName = 'inventory';
                } else if (route.name === 'Reports') {
                  iconName = 'analytics';
                } else if (route.name === 'Settings') {
                  iconName = 'settings';
                }

                return <Icon name={iconName} size={size} color={color} />;
              },
              tabBarActiveTintColor: '#6200EE',
              tabBarInactiveTintColor: 'gray',
              headerShown: false,
            })}>
            <Tab.Screen name="Inventory" component={InventoryStack} />
            <Tab.Screen name="Reports" component={ReportsScreen} />
            <Tab.Screen name="Settings" component={SettingsScreen} />
          </Tab.Navigator>
        </NavigationContainer>
      </MedicineProvider>
    </PaperProvider>
  );
};

export default App;