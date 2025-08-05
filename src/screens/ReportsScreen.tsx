import React, {useState, useEffect} from 'react';
import {
  View,
  ScrollView,
  StyleSheet,
  Dimensions,
} from 'react-native';
import {
  Card,
  Title,
  Paragraph,
  Text,
  Chip,
  Divider,
  List,
  ProgressBar,
} from 'react-native-paper';
import {LineChart, PieChart, BarChart} from 'react-native-chart-kit';
import {useMedicine} from '../context/MedicineContext';
import {Medicine, MedicineCategory, TransactionType} from '../types/Medicine';

const screenWidth = Dimensions.get('window').width;

const ReportsScreen: React.FC = () => {
  const {
    state,
    getLowStockMedicines,
    getExpiringMedicines,
  } = useMedicine();

  const [totalValue, setTotalValue] = useState(0);
  const [categoryData, setCategoryData] = useState<any[]>([]);
  const [stockStatusData, setStockStatusData] = useState<any[]>([]);

  useEffect(() => {
    calculateStats();
  }, [state.medicines, state.transactions]);

  const calculateStats = () => {
    // Calculate total inventory value
    const value = state.medicines.reduce((sum, med) => sum + (med.quantity * med.unitPrice), 0);
    setTotalValue(value);

    // Group medicines by category
    const categories = state.medicines.reduce((acc, med) => {
      acc[med.category] = (acc[med.category] || 0) + 1;
      return acc;
    }, {} as Record<string, number>);

    const categoryChartData = Object.entries(categories).map(([category, count], index) => ({
      name: category.length > 15 ? category.substring(0, 15) + '...' : category,
      count,
      color: getColorForIndex(index),
      legendFontColor: '#333',
      legendFontSize: 12,
    }));

    setCategoryData(categoryChartData);

    // Stock status analysis
    const lowStock = getLowStockMedicines().length;
    const expiring = getExpiringMedicines(30).length;
    const normal = state.medicines.length - lowStock - expiring;

    setStockStatusData([
      {name: 'Normal', count: normal, color: '#4CAF50', legendFontColor: '#333', legendFontSize: 12},
      {name: 'Low Stock', count: lowStock, color: '#FF9800', legendFontColor: '#333', legendFontSize: 12},
      {name: 'Expiring Soon', count: expiring, color: '#F44336', legendFontColor: '#333', legendFontSize: 12},
    ]);
  };

  const getColorForIndex = (index: number): string => {
    const colors = ['#6200EE', '#03DAC6', '#FF6F00', '#E91E63', '#4CAF50', '#2196F3', '#FF5722', '#9C27B0'];
    return colors[index % colors.length];
  };

  const getTransactionTrends = () => {
    const last7Days = Array.from({length: 7}, (_, i) => {
      const date = new Date();
      date.setDate(date.getDate() - i);
      return {
        date: date.toISOString().split('T')[0],
        label: date.toLocaleDateString('en', {weekday: 'short'}),
        stockIn: 0,
        stockOut: 0,
      };
    }).reverse();

    state.transactions.forEach(transaction => {
      const transactionDate = transaction.timestamp.toISOString().split('T')[0];
      const dayData = last7Days.find(day => day.date === transactionDate);
      
      if (dayData) {
        if (transaction.type === TransactionType.STOCK_IN) {
          dayData.stockIn += transaction.quantity;
        } else if (transaction.type === TransactionType.STOCK_OUT) {
          dayData.stockOut += transaction.quantity;
        }
      }
    });

    return {
      labels: last7Days.map(day => day.label),
      datasets: [
        {
          data: last7Days.map(day => day.stockIn),
          color: (opacity = 1) => `rgba(76, 175, 80, ${opacity})`,
          strokeWidth: 2,
        },
        {
          data: last7Days.map(day => day.stockOut),
          color: (opacity = 1) => `rgba(244, 67, 54, ${opacity})`,
          strokeWidth: 2,
        },
      ],
      legend: ['Stock In', 'Stock Out'],
    };
  };

  const getTopValueMedicines = () => {
    return [...state.medicines]
      .sort((a, b) => (b.quantity * b.unitPrice) - (a.quantity * a.unitPrice))
      .slice(0, 5);
  };

  const getTopQuantityMedicines = () => {
    return [...state.medicines]
      .sort((a, b) => b.quantity - a.quantity)
      .slice(0, 5);
  };

  const chartConfig = {
    backgroundColor: '#ffffff',
    backgroundGradientFrom: '#ffffff',
    backgroundGradientTo: '#ffffff',
    decimalPlaces: 0,
    color: (opacity = 1) => `rgba(98, 0, 238, ${opacity})`,
    labelColor: (opacity = 1) => `rgba(0, 0, 0, ${opacity})`,
    style: {
      borderRadius: 16,
    },
    propsForDots: {
      r: '4',
      strokeWidth: '2',
      stroke: '#6200EE',
    },
  };

  const transactionData = getTransactionTrends();

  return (
    <ScrollView style={styles.container}>
      {/* Summary Cards */}
      <View style={styles.summaryRow}>
        <Card style={[styles.summaryCard, styles.primaryCard]}>
          <Card.Content>
            <Title style={styles.summaryNumber}>{state.medicines.length}</Title>
            <Text style={styles.summaryLabel}>Total Medicines</Text>
          </Card.Content>
        </Card>

        <Card style={[styles.summaryCard, styles.valueCard]}>
          <Card.Content>
            <Title style={styles.summaryNumber}>${totalValue.toFixed(2)}</Title>
            <Text style={styles.summaryLabel}>Total Value</Text>
          </Card.Content>
        </Card>
      </View>

      <View style={styles.summaryRow}>
        <Card style={[styles.summaryCard, styles.warningCard]}>
          <Card.Content>
            <Title style={styles.summaryNumber}>{getLowStockMedicines().length}</Title>
            <Text style={styles.summaryLabel}>Low Stock</Text>
          </Card.Content>
        </Card>

        <Card style={[styles.summaryCard, styles.dangerCard]}>
          <Card.Content>
            <Title style={styles.summaryNumber}>{getExpiringMedicines(30).length}</Title>
            <Text style={styles.summaryLabel}>Expiring Soon</Text>
          </Card.Content>
        </Card>
      </View>

      {/* Stock Trends Chart */}
      {state.transactions.length > 0 && (
        <Card style={styles.chartCard}>
          <Card.Content>
            <Title style={styles.chartTitle}>Stock Movement (Last 7 Days)</Title>
            <LineChart
              data={transactionData}
              width={screenWidth - 64}
              height={220}
              chartConfig={chartConfig}
              bezier
              style={styles.chart}
            />
          </Card.Content>
        </Card>
      )}

      {/* Category Distribution */}
      {categoryData.length > 0 && (
        <Card style={styles.chartCard}>
          <Card.Content>
            <Title style={styles.chartTitle}>Medicine Categories</Title>
            <PieChart
              data={categoryData}
              width={screenWidth - 64}
              height={220}
              chartConfig={chartConfig}
              accessor="count"
              backgroundColor="transparent"
              paddingLeft="15"
              center={[10, 10]}
              style={styles.chart}
            />
          </Card.Content>
        </Card>
      )}

      {/* Stock Status */}
      {stockStatusData.length > 0 && (
        <Card style={styles.chartCard}>
          <Card.Content>
            <Title style={styles.chartTitle}>Stock Status Overview</Title>
            <PieChart
              data={stockStatusData}
              width={screenWidth - 64}
              height={220}
              chartConfig={chartConfig}
              accessor="count"
              backgroundColor="transparent"
              paddingLeft="15"
              center={[10, 10]}
              style={styles.chart}
            />
          </Card.Content>
        </Card>
      )}

      {/* Top Medicines by Value */}
      <Card style={styles.listCard}>
        <Card.Content>
          <Title style={styles.listTitle}>Top Medicines by Value</Title>
          {getTopValueMedicines().map((medicine, index) => (
            <View key={medicine.id}>
              <List.Item
                title={medicine.name}
                description={`${medicine.quantity} units × $${medicine.unitPrice.toFixed(2)}`}
                right={() => (
                  <View style={styles.valueContainer}>
                    <Text style={styles.valueText}>
                      ${(medicine.quantity * medicine.unitPrice).toFixed(2)}
                    </Text>
                    <Chip mode="outlined" compact style={styles.rankChip}>
                      #{index + 1}
                    </Chip>
                  </View>
                )}
              />
              {index < getTopValueMedicines().length - 1 && <Divider />}
            </View>
          ))}
        </Card.Content>
      </Card>

      {/* Top Medicines by Quantity */}
      <Card style={styles.listCard}>
        <Card.Content>
          <Title style={styles.listTitle}>Highest Stock Quantities</Title>
          {getTopQuantityMedicines().map((medicine, index) => (
            <View key={medicine.id}>
              <List.Item
                title={medicine.name}
                description={medicine.manufacturer}
                right={() => (
                  <View style={styles.quantityContainer}>
                    <Text style={styles.quantityText}>{medicine.quantity} units</Text>
                    <ProgressBar 
                      progress={medicine.quantity / Math.max(...state.medicines.map(m => m.quantity))} 
                      color="#6200EE"
                      style={styles.progressBar}
                    />
                  </View>
                )}
              />
              {index < getTopQuantityMedicines().length - 1 && <Divider />}
            </View>
          ))}
        </Card.Content>
      </Card>

      {/* Low Stock Alert */}
      {getLowStockMedicines().length > 0 && (
        <Card style={styles.alertCard}>
          <Card.Content>
            <Title style={styles.alertTitle}>⚠️ Low Stock Alert</Title>
            {getLowStockMedicines().slice(0, 5).map((medicine, index) => (
              <View key={medicine.id}>
                <List.Item
                  title={medicine.name}
                  description={`Current: ${medicine.quantity} | Min: ${medicine.minStockLevel}`}
                  left={() => <Text style={styles.urgentIcon}>🔴</Text>}
                />
                {index < Math.min(getLowStockMedicines().length, 5) - 1 && <Divider />}
              </View>
            ))}
            {getLowStockMedicines().length > 5 && (
              <Paragraph style={styles.moreText}>
                +{getLowStockMedicines().length - 5} more medicines need restocking
              </Paragraph>
            )}
          </Card.Content>
        </Card>
      )}

      {/* Expiring Soon Alert */}
      {getExpiringMedicines(30).length > 0 && (
        <Card style={styles.alertCard}>
          <Card.Content>
            <Title style={styles.alertTitle}>⏰ Expiring Soon</Title>
            {getExpiringMedicines(30).slice(0, 5).map((medicine, index) => {
              const daysUntilExpiry = Math.ceil((medicine.expiryDate.getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));
              return (
                <View key={medicine.id}>
                  <List.Item
                    title={medicine.name}
                    description={`Expires in ${daysUntilExpiry} days`}
                    left={() => <Text style={styles.warningIcon}>🟠</Text>}
                  />
                  {index < Math.min(getExpiringMedicines(30).length, 5) - 1 && <Divider />}
                </View>
              );
            })}
            {getExpiringMedicines(30).length > 5 && (
              <Paragraph style={styles.moreText}>
                +{getExpiringMedicines(30).length - 5} more medicines expiring soon
              </Paragraph>
            )}
          </Card.Content>
        </Card>
      )}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  summaryRow: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingTop: 16,
    gap: 12,
  },
  summaryCard: {
    flex: 1,
    elevation: 2,
  },
  primaryCard: {
    backgroundColor: '#E8EAF6',
  },
  valueCard: {
    backgroundColor: '#E8F5E8',
  },
  warningCard: {
    backgroundColor: '#FFF8E1',
  },
  dangerCard: {
    backgroundColor: '#FFEBEE',
  },
  summaryNumber: {
    fontSize: 24,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#333',
  },
  summaryLabel: {
    fontSize: 12,
    textAlign: 'center',
    color: '#666',
    marginTop: 4,
  },
  chartCard: {
    margin: 16,
    elevation: 2,
  },
  chartTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 16,
    color: '#6200EE',
  },
  chart: {
    marginVertical: 8,
    borderRadius: 16,
  },
  listCard: {
    margin: 16,
    marginTop: 8,
    elevation: 2,
  },
  listTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 12,
    color: '#6200EE',
  },
  valueContainer: {
    alignItems: 'flex-end',
  },
  valueText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2E7D32',
    marginBottom: 4,
  },
  rankChip: {
    height: 24,
    backgroundColor: '#E3F2FD',
  },
  quantityContainer: {
    alignItems: 'flex-end',
    width: 120,
  },
  quantityText: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  progressBar: {
    width: 100,
    height: 4,
  },
  alertCard: {
    margin: 16,
    marginTop: 8,
    elevation: 2,
    backgroundColor: '#FFF3E0',
  },
  alertTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 12,
    color: '#E65100',
  },
  urgentIcon: {
    fontSize: 16,
    alignSelf: 'center',
  },
  warningIcon: {
    fontSize: 16,
    alignSelf: 'center',
  },
  moreText: {
    fontSize: 14,
    fontStyle: 'italic',
    color: '#BF360C',
    textAlign: 'center',
    marginTop: 8,
  },
});

export default ReportsScreen;